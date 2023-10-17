<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Http\Requests\CreateTicketRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\TicketType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;

class Ticket extends Component
{
    use Actions;

    public array $ticket;

    public array $availableStates;

    public array $additionalColumns = [];

    public array $ticketTypes;

    public array $states;

    public string $tab = 'features.comments.comments';

    public function mount(int|string $id): void
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

        if ($id === 'new') {
            $ticketModel = new \FluxErp\Models\Ticket(
                array_fill_keys(
                    array_keys((new CreateTicketRequest())->rules()),
                    null
                )
            );

            $ticketModel->id = 0;
            $ticketModel->authenticatable_type = Auth::user()->getMorphClass();
            $ticketModel->authenticatable_id = Auth::user()->getAuthIdentifier();
        } else {
            $ticketModel = \FluxErp\Models\Ticket::query()
                ->with([
                    'users:id',
                    'users.media',
                    'ticketType:id,name',
                    'authenticatable',
                ])
                ->whereKey($id)
                ->firstOrFail();
        }


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

    public function updateAdditionalColumns(?int $id): void
    {
        $this->additionalColumns = AdditionalColumn::query()
            ->where('is_frontend_visible', true)
            ->where(function (Builder $query) use ($id) {
                $query->where('model_type', \FluxErp\Models\Ticket::class)
                    ->when($id, function (Builder $query) use ($id) {
                        $query->orWhere(function (Builder $query) use ($id) {
                            $query->where('model_type', TicketType::class)
                                ->where('model_id', $id);
                        });
                    });
            })
            ->get()
            ->toArray();
    }

    public function save(): bool|Redirector
    {
        $action = $this->ticket['id'] ? UpdateTicket::class : CreateTicket::class;

        if ($action === CreateTicket::class) {
            unset($this->ticket['uuid'], $this->ticket['model_type'], $this->ticket['model_id']);
        }

        try {
            $ticket = $action::make($this->ticket)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        if ($action === CreateTicket::class) {
            return redirect(route('tickets.id', ['id' => $ticket->id]));
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

        $this->skipRender();

        return true;
    }

    public function delete(): void
    {
        $this->skipRender();

        try {
            DeleteTicket::make($this->ticket)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirect(route('tickets'));
    }
}
