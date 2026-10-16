<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Pivots\TicketUser;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Database\Eloquent\Collection;

class UnassignedTickets extends MyTickets
{
    use Widgetable;

    public static function getCategory(): ?string
    {
        return 'Tickets';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    protected function getTickets(): Collection
    {
        return resolve_static(Ticket::class, 'query')
            ->whereDoesntHave('users')
            ->with('authenticatable:id,name')
            ->whereNotIn('state', TicketState::endStateKeys())
            ->orderByRaw("state = 'escalated' DESC")
            ->orderBy('created_at')
            ->limit($this->limit + 1)
            ->get([
                'id',
                'title',
                'description',
                'state',
                'created_at',
                'authenticatable_type',
                'authenticatable_id',
            ]);
    }

    protected function liveUpdates(): array
    {
        return [
            $this->modelChannel(Ticket::class) => [
                Ticket::class => ['created', 'updated', 'deleted'],
            ],
            $this->modelChannel(TicketUser::class) => [
                TicketUser::class => ['created', 'deleted'],
            ],
        ];
    }
}
