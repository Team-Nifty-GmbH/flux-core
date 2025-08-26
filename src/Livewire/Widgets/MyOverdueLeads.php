<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Models\LeadState;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class MyOverdueLeads extends Component implements HasWidgetOptions
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
        $endStates = $this->getEndStates();

        return view(
            'flux::livewire.widgets.my-leads',
            [
                'leads' => auth()
                    ->user()
                    ->leads()
                    ->with(['address:id,name', 'leadState:id,name,color'])
                    ->whereIntegerInRaw('lead_state_id', $endStates)
                    ->where('end', '<', now())
                    ->orderByRaw('ISNULL(end), end ASC')
                    ->orderByDesc('probability_percentage')
                    ->orderByDesc('score')
                    ->get(),
            ]
        );
    }

    public function options(): array
    {
        return [
            [
                'label' => __('Show'),
                'method' => 'show',
            ],
        ];
    }

    public function placeholder(): View|Factory
    {
        return view('flux::livewire.placeholders.horizontal-bar');
    }

    #[Renderless]
    public function show(): void
    {
        $endStates = $this->getEndStates();

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query->where('user_id', auth()->id())
                ->whereIntegerInRaw('lead_state_id', $endStates)
                ->where('end', '<', now())
                ->orderByRaw('ISNULL(end), end ASC')
                ->orderByDesc('probability_percentage')
                ->orderByDesc('score'),
            __(static::getLabel()),
        )
            ->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }

    protected function getEndStates(): array
    {
        return resolve_static(LeadState::class, 'query')
            ->whereNot('is_won', true)
            ->whereNot('is_lost', true)
            ->pluck('id')
            ->toArray();
    }
}
