<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\LeadState;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyLeads extends Component
{
    use Widgetable;

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
        $endStates = resolve_static(LeadState::class, 'query')
            ->whereNot('is_won', true)
            ->whereNot('is_lost', true)
            ->pluck('id');

        return view(
            'flux::livewire.widgets.my-leads',
            [
                'leads' => auth()
                    ->user()
                    ->leads()
                    ->with(['address:id,name', 'leadState:id,name,color'])
                    ->whereIntegerInRaw('lead_state_id', $endStates)
                    ->orderByRaw('ISNULL(end), end ASC')
                    ->orderByDesc('probability_percentage')
                    ->orderByDesc('score')
                    ->get(),
            ]
        );
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }
}
