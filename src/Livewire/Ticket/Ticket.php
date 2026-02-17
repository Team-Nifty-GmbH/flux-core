<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\TicketForm;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket as TicketModel;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Ticket extends Component
{
    use Actions, WithTabs;

    public bool $authorTypeContact = true;

    public array $availableStates;

    public array $states;

    public string $tab = 'ticket.comments';

    public TicketForm $ticket;

    public array $ticketTypes;

    public function mount(int $id): void
    {
        $this->fetchTicket($id);
    }

    public function render(): View
    {
        return view('flux::livewire.ticket.ticket');
    }

    #[Renderless]
    public function assignToMe(): void
    {
        $this->ticket->users = array_unique(array_merge($this->ticket->users, [auth()->id()]));
        $this->save();
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

    #[Renderless]
    public function fetchTicket(?int $id = null): void
    {
        $id ??= $this->ticket->id;

        $states = app(TicketModel::class)->getStatesFor('state');
        $this->states = $states->map(fn (string $state) => [
            'label' => __($state),
            'name' => $state,
        ])->toArray();

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

    public function getTabs(): array
    {
        return [
            TabButton::make('ticket.comments')
                ->text(__('Comments'))
                ->wireModel('ticket.id')
                ->isLivewireComponent(),
            TabButton::make('ticket.communication')
                ->text(__('Communication'))
                ->wireModel('ticket.id')
                ->isLivewireComponent(),
            TabButton::make('ticket.activities')
                ->text(__('Activities'))
                ->wireModel('ticket.id')
                ->isLivewireComponent(),
        ];
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

        $this->toast()->success(__(':model saved', ['model' => __('Ticket')]))->send();

        return true;
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
            component.request.url = '$route';
        JS);

        $this->skipRender();
    }
}
