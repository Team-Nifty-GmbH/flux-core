<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\States\Ticket\TicketState;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyTickets extends Component
{
    use Widgetable;

    public function render(): View|Factory
    {
        $endStates = TicketState::all()
            ->filter(fn ($state) => $state::$isEndState)
            ->keys()
            ->toArray();

        return view(
            'flux::livewire.widgets.my-tickets',
            [
                'tickets' => auth()
                    ->user()
                    ->tickets()
                    ->with('authenticatable:id,name')
                    ->whereNotIn('state', $endStates)
                    ->orderByRaw("state = 'escalated' DESC")
                    ->orderBy('created_at')
                    ->get(),
            ]
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }
}
