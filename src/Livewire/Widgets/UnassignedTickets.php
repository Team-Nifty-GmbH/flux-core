<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Collection;

class UnassignedTickets extends MyTickets
{
    use Widgetable;

    protected function getListeners(): array
    {
        return array_merge(
            [
                'echo-private:' . resolve_static(Ticket::class, 'getBroadcastChannel')
                    . ',.TicketCreated' => '$refresh',
            ],
            parent::getListeners()
        );
    }

    protected function getTickets(): Collection
    {
        return $this->tickets ?? resolve_static(Ticket::class, 'query')
            ->whereDoesntHave('users')
            ->with('authenticatable:id,name')
            ->whereNotIn(
                'state',
                TicketState::all()
                    ->filter(fn ($state) => $state::$isEndState)
                    ->keys()
                    ->toArray()
            )
            ->orderByRaw("state = 'escalated' DESC")
            ->orderBy('created_at')
            ->get();
    }
}
