<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Collection;

class UnassignedTickets extends MyTickets
{
    use Widgetable;

    public static function dashboardComponent(): string
    {
        return Dashboard::class;
    }

    protected function getListeners(): array
    {
        return $this->rememberedEventListeners = array_merge(
            $this->rememberedEventListeners ?? [],
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
