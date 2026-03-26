<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\Escalated;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class EscalatedTickets extends Component
{
    use Widgetable;

    public int $count = 0;

    public int $limit = 25;

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
        $this->count = resolve_static(Ticket::class, 'query')
            ->where('state', Escalated::$name)
            ->count();

        $tickets = $this->getTickets();

        return view(
            'flux::livewire.widgets.escalated-tickets',
            [
                'tickets' => $tickets,
                'hasMore' => $this->count > $this->limit,
            ]
        );
    }

    public function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(Ticket::class, 'getBroadcastChannel') . ',.TicketUpdated' => '$refresh',
            'echo-private:' . resolve_static(Ticket::class, 'getBroadcastChannel') . ',.TicketCreated' => '$refresh',
        ];
    }

    public function loadMore(): void
    {
        $this->limit += 25;
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    protected function getTickets(): Collection
    {
        return resolve_static(Ticket::class, 'query')
            ->where('state', Escalated::$name)
            ->with('authenticatable:id,name')
            ->orderBy('created_at')
            ->limit($this->limit)
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
}
