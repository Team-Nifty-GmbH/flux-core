<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class MyTickets extends Component
{
    use Widgetable;

    public ?array $rememberedEventListeners = null;

    protected ?Collection $tickets = null;

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
        return view(
            'flux::livewire.widgets.tickets',
            [
                'tickets' => $this->getTickets(),
            ]
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    protected function getListeners(): array
    {
        return $this->rememberedEventListeners = array_merge(
            $this->rememberedEventListeners ?? [],
            $this->getTickets()
                ->mapWithKeys(fn (Ticket $ticket, int $key) => [
                    'echo-private:' . $ticket->broadcastChannel() . ',.TicketUpdated' => '$refresh',
                ])
                ->toArray() ?? []
        );
    }

    protected function getTickets(): Collection
    {
        return $this->tickets ?? auth()
            ->user()
            ->tickets()
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
