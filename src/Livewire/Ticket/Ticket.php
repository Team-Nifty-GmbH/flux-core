<?php

namespace FluxErp\Livewire\Ticket;

use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;

class Ticket extends Component
{
    use Actions, WithTabs;

    public array $ticket;

    public array $availableStates;

    public array $additionalColumns = [];

    public array $ticketTypes;

    public array $states;

    public bool $authorTypeContact = true;

    public string $tab = 'features.comments.comments';

    public function mount(int $id): void
    {
        $states = \FluxErp\Models\Ticket::getStatesFor('state');
        $this->states = array_map(function ($item) {
            return [
                'label' => __($item),
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

        $this->authorTypeContact = $this->ticket['authenticatable_type'] === Address::class;

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
            TabButton::make('features.comments.comments')->label(__('Comments')),
            TabButton::make('features.activities')->label(__('Activities')),
        ];
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

    public function updatedAuthorTypeContact(): void
    {
        $this->ticket['authenticatable_type'] = $this->authorTypeContact ? Address::class : User::class;
        $this->ticket['authenticatable_id'] = null;

        $this->skipRender();
    }
}
