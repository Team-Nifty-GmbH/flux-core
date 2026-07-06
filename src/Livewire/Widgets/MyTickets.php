<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasApiResponse;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Livewire\Widget\RespondsToApiRequests;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class MyTickets extends Component implements HasApiResponse
{
    use RespondsToApiRequests, Widgetable;

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

    public function toApiResponse(): array
    {
        return $this->getTickets()
            ->take($this->limit)
            ->map(fn (Ticket $ticket): array => [
                'id' => $ticket->getKey(),
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title,
                'state' => $ticket->state::$name,
                'url' => $ticket->getUrl(),
                'authenticatable' => $ticket->authenticatable?->getLabel(),
            ])
            ->toArray();
    }

    protected function apiRules(): array
    {
        return [
            'limit' => ['integer', 'min:1'],
        ];
    }

    protected function getTickets(): Collection
    {
        return auth()
            ->user()
            ->tickets()
            ->with('authenticatable:id,name')
            ->whereNotIn('state', TicketState::endStateKeys())
            ->orderByRaw("state = 'escalated' DESC")
            ->orderBy('created_at')
            ->limit($this->limit + 1)
            ->get([
                'id',
                'ticket_number',
                'title',
                'description',
                'state',
                'created_at',
                'authenticatable_type',
                'authenticatable_id',
            ]);
    }
}
