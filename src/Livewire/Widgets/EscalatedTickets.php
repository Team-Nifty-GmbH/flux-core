<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\Escalated;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EscalatedTickets extends Component
{
    use Widgetable;

    public int $count = 0;

    public static function getCategory(): ?string
    {
        return 'Tickets';
    }

    public static function getDefaultHeight(): int
    {
        return 3;
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public function render(): View|Factory
    {
        $tickets = resolve_static(Ticket::class, 'query')
            ->where('state', Escalated::$name)
            ->with('authenticatable:id,name')
            ->orderBy('created_at')
            ->get();

        $this->count = $tickets->count();

        return view(
            'flux::livewire.widgets.escalated-tickets',
            ['tickets' => $tickets]
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Ticket::class, 'getBroadcastChannel')
                . ',.TicketUpdated' => '$refresh',
            'echo-private:' . resolve_static(Ticket::class, 'getBroadcastChannel')
                . ',.TicketCreated' => '$refresh',
        ];
    }
}
