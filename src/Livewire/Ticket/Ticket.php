<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\TicketForm;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket as TicketModel;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Ticket extends Component
{
    use Actions, WithTabs;

    public TicketForm $ticket;

    public array $availableStates;

    public array $ticketTypes;

    public array $states;

    public bool $authorTypeContact = true;

    public string $tab = 'ticket.comments';

    public function mount(int $id): void
    {
        $states = app(TicketModel::class)->getStatesFor('state');
        $this->states = array_map(function ($item) {
            return [
                'label' => __($item),
                'name' => $item,
            ];
        }, $states->toArray());

        $this->ticketTypes = resolve_static(TicketType::class, 'query')
            ->select(['id', 'name'])
            ->get()
            ->toArray();

        $ticketModel = resolve_static(TicketModel::class, 'query')
            ->with([
                'users:id',
                'users.media',
                'ticketType:id,name',
                'authenticatable',
                'meta:id,additional_column_id,model_type,model_id,key,value',
            ])
            ->whereKey($id)
            ->firstOrFail();

        $ticketModel->state = $ticketModel->state ?:
            resolve_static(TicketModel::class, 'getDefaultStateFor', ['state']);

        $this->ticket->fill($ticketModel);

        $this->authorTypeContact = $this->ticket->authenticatable_type === morph_alias(Address::class);

        $this->availableStates = collect($this->states)
            ->whereIn(
                'name',
                array_merge(
                    [$this->ticket->state],
                    $ticketModel->state->transitionableStates()
                )
            )
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.ticket.ticket');
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('ticket.comments')
                ->text(__('Comments'))
                ->isLivewireComponent(),
            TabButton::make('ticket.communication')
                ->text(__('Communication'))
                ->wireModel('ticket.id')
                ->isLivewireComponent(),
            TabButton::make('ticket.activities')
                ->text(__('Activities'))
                ->isLivewireComponent(),
        ];
    }

    public function updateAdditionalColumns(?int $id): void
    {
        $this->additionalColumns = resolve_static(AdditionalColumn::class, 'query')
            ->where('is_frontend_visible', true)
            ->where(function (Builder $query) use ($id) {
                $query->where('model_type', morph_alias(TicketModel::class))
                    ->when($id, function (Builder $query) use ($id) {
                        $query->orWhere(function (Builder $query) use ($id) {
                            $query->where('model_type', morph_alias(TicketType::class))
                                ->where('model_id', $id);
                        });
                    });
            })
            ->get()
            ->toArray();
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->ticket->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->availableStates = collect($this->states)
            ->whereIn(
                'name',
                array_merge(
                    [$this->ticket->state],
                    $this->ticket->getActionResult()->state->transitionableStates()
                )
            )
            ->toArray();

        $this->notification()->success(__(':model saved', ['model' => __('Ticket')]))->send();

        return true;
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            $this->ticket->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirect(route('tickets'), navigate: true);
    }

    public function updatedAuthorTypeContact(): void
    {
        $this->ticket->authenticatable_type = morph_alias($this->authorTypeContact
            ? Address::class
            : User::class
        );
        $this->ticket->authenticatable_id = null;
        $route = route('search', $this->ticket->authenticatable_type);

        $this->js(<<<JS
            let component = Alpine.\$data(document.getElementById('author-select').querySelector('[x-data]'));
            component.request.api = '$route';
        JS);

        $this->skipRender();
    }
}
