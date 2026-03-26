<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class MyTickets extends Component
{
    use Widgetable;

    public int $limit = 25;

    public static function getCategory(): ?string
    {
        return 'Tickets';
    }

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function render(): View|Factory
    {
        $tickets = $this->getTickets();

        return view(
            'flux::livewire.widgets.tickets',
            [
                'tickets' => $tickets->take($this->limit),
                'hasMore' => $tickets->count() > $this->limit,
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
        return auth()
            ->user()
            ->tickets()
            ->with('authenticatable:id,name')
            ->whereNotIn(
                'state',
                TicketState::all()
                    ->filter(fn (string $state): bool => $state::$isEndState)
                    ->keys()
                    ->toArray()
            )
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
}
