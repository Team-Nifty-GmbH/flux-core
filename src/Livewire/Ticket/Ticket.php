<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket as TicketModel;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Ticket extends Component
{
    use Actions, WithTabs;

    public array $ticket;

    public array $availableStates;

    public array $additionalColumns = [];

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
            ])
            ->whereKey($id)
            ->firstOrFail();

        $ticketModel->state = $ticketModel->state ?:
            resolve_static(TicketModel::class, 'getDefaultStateFor', ['state']);

        $this->additionalColumns = resolve_static(AdditionalColumn::class, 'query')
            ->where('is_frontend_visible', true)
            ->where(function (Builder $query) use ($ticketModel) {
                $query->where('model_type', morph_alias(TicketModel::class))
                    ->when($ticketModel->ticket_type_id, function (Builder $query) use ($ticketModel) {
                        $query->orWhere(function (Builder $query) use ($ticketModel) {
                            $query->where('model_type', morph_alias(TicketType::class))
                                ->where('model_id', $ticketModel->ticket_type_id);
                        });
                    });
            })
            ->get()
            ->toArray();

        $this->ticket = $ticketModel->toArray();
        $this->ticket['authenticatable']['avatar_url'] = $ticketModel->authenticatable?->getAvatarUrl();
        $this->ticket['authenticatable']['name'] = $ticketModel->authenticatable?->getLabel();
        $this->ticket['users'] = $ticketModel->users->pluck('id')->toArray();

        $this->authorTypeContact = $this->ticket['authenticatable_type'] === morph_alias(Address::class);

        $this->availableStates = collect($this->states)
            ->whereIn(
                'name',
                array_merge(
                    [$this->ticket['state']],
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
                ->label(__('Comments'))
                ->isLivewireComponent(),
            TabButton::make('ticket.communication')
                ->label(__('Communication'))
                ->wireModel('ticket.id')
                ->isLivewireComponent(),
            TabButton::make('ticket.activities')
                ->label(__('Activities'))
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
            $ticket = UpdateTicket::make($this->ticket)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->ticket = array_merge($this->ticket, $ticket->load('authenticatable')->toArray());
        $this->ticket['authenticatable']['avatar_url'] = $ticket->authenticatable->getAvatarUrl();
        $this->ticket['authenticatable']['name'] = $ticket->authenticatable->getLabel();

        $this->availableStates = collect($this->states)
            ->whereIn(
                'name',
                array_merge(
                    [$this->ticket['state']],
                    $ticket->state->transitionableStates()
                )
            )
            ->toArray();

        $this->notification()->success(__(':model saved', ['model' => __('Ticket')]));

        return true;
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            DeleteTicket::make($this->ticket)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirect(route('tickets'), navigate: true);
    }

    public function updatedAuthorTypeContact(): void
    {
        $this->ticket['authenticatable_type'] = resolve_static(
            $this->authorTypeContact ? Address::class : User::class,
            'class'
        );
        $this->ticket['authenticatable_id'] = null;

        $this->skipRender();
    }
}
