<?php

namespace FluxErp\Http\Livewire\Ticket;

use FluxErp\Http\Requests\CreateTicketRequest;
use FluxErp\Http\Requests\UpdateTicketRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
use FluxErp\Services\TicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class Ticket extends Component
{
    use Actions;

    public array $ticket;

    public array $availableStates;

    public array $additionalColumns = [];

    public string $ticketState;

    public array $ticketTypes;

    public array $states;

    public function getRules(): array
    {
        return Arr::prependKeysWith(
            ($this->ticket['id']
                ? new UpdateTicketRequest()
                : new CreateTicketRequest()
            )->rules(),
            'ticket.'
        );
    }

    public function mount(int $id): void
    {
        $states = \FluxErp\Models\Ticket::getStatesFor('state');

        $this->states = array_map(function ($item) {
            return [
                'label' => __(ucfirst(str_replace('_', ' ', $item))),
                'name' => $item,
            ];
        }, $states->toArray());

        $this->ticketTypes = TicketType::query()
            ->select(['id', 'name'])
            ->get()
            ->toArray();

        $ticketModel = \FluxErp\Models\Ticket::query()
            ->with([
                'users:id',
                'users.media',
                'ticketType:id,name',
                'authenticatable',
            ])
            ->whereKey($id)
            ->firstOrFail();

        $ticketModel->state = $ticketModel->state ?: \FluxErp\Models\Ticket::getDefaultStateFor('state');

        $this->additionalColumns = AdditionalColumn::query()
            ->where('is_frontend_visible', true)
            ->where(function (Builder $query) use ($ticketModel) {
                $query->where('model_type', \FluxErp\Models\Ticket::class)
                    ->when($ticketModel->ticket_type_id, function (Builder $query) use ($ticketModel) {
                        $query->orWhere(function (Builder $query) use ($ticketModel) {
                            $query->where('model_type', TicketType::class)
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

        $this->ticketState = $this->ticket['state'];

        $this->availableStates = collect($this->states)
            ->whereIn(
                'name',
                array_merge(
                    [$this->ticketState],
                    $ticketModel->state->transitionableStates()
                )
            )
            ->toArray();
    }

    public function render(): View
    {
        return view('flux::livewire.ticket.ticket');
    }

    public function updatedTicketState(): void
    {
        if (! user_can('api.tickets.put')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        $ticketModel = \FluxErp\Models\Ticket::query()
            ->whereKey($this->ticket['id'])
            ->firstOrNew()
            ->state
            ->transitionTo($this->ticketState);

        $this->availableStates = collect($this->states)
            ->whereIn(
                'name',
                array_merge(
                    [$this->ticketState],
                    $ticketModel->state->transitionableStates()
                )
            )
            ->toArray();

        $this->skipRender();
    }

    public function updatedTicketUsers(): void
    {
        $this->save();
    }

    public function updatedTicketTicketTypeID(): void
    {
        $this->save();
    }

    public function changeAuthor(int $id): void
    {
        if (! user_can('api.tickets.put')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        $ticketService = new TicketService();
        $this->ticket['authenticatable_id'] = $id;

        $validated = $this->validate()['ticket'];
        $updatedTicket = $ticketService->update($validated)->load('authenticatable');

        $this->ticket = array_merge($this->ticket, $updatedTicket->toArray());
        $this->ticket['authenticatable']['avatar_url'] = $updatedTicket->authenticatable->getAvatarUrl();
        $this->ticket['authenticatable']['name'] = $updatedTicket->authenticatable->getLabel();

        $this->skipRender();
    }

    public function save(): void
    {
        if (! user_can('api.tickets.put')) {
            $this->notification()->error(__('You dont have the permission to do that.'));

            return;
        }

        $ticketService = new TicketService();
        $validated = $this->validate()['ticket'];

        $ticketService->update($validated);

        $this->skipRender();
    }
}
