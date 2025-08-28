<?php

namespace FluxErp\Livewire\Widgets;

use Closure;
use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Livewire\Support\Widgets\Charts\CircleChart;
use FluxErp\Models\Lead;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class OverallLeadWonLostRatio extends CircleChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget;

    public ?array $chart = [
        'type' => 'donut',
    ];

    public bool $showTotals = true;

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateChart();
        $this->updateData();
    }

    public function calculateChart(): void
    {
        $data = resolve_static(Lead::class, 'query')
            ->where($this->getBaseFilter(
                $this->getStart()->toDateTimeString(),
                $this->getEnd()->toDateTimeString()
            ))
            ->whereHas('leadState', fn (Builder $query) => $query->where('is_won', true)
                ->orWhere('is_lost', true))
            ->with('leadState:id,is_won')
            ->get()
            ->groupBy(fn ($lead) => $lead->leadState->is_won ? __('Won Leads') : __('Lost Leads'))
            ->map(fn ($group, $key) => [
                'name' => $key,
                'total' => $group->count(),
            ])
            ->values()
            ->toArray();

        $this->labels = array_column($data, 'name');
        $this->series = array_column($data, 'total');
    }

    #[Renderless]
    public function options(): array
    {
        return [
            [
                'label' => __('Won leads'),
                'method' => 'redirectWonLeads',
            ],
            [
                'label' => __('Lost Leads'),
                'method' => 'redirectLostLeads',
            ],
        ];
    }

    #[Renderless]
    public function redirectLostLeads(): void
    {
        $this->redirectWithFilter(
            fn (Builder $query) => $query->whereHas('leadState', fn ($sq) => $sq->where('is_lost', true)),
            __('Lost leads')
        );
    }

    #[Renderless]
    public function redirectWonLeads(): void
    {
        $this->redirectWithFilter(
            fn (Builder $query) => $query->whereHas('leadState', fn ($sq) => $sq->where('is_won', true)),
            __('Won leads')
        );
    }

    protected function getBaseFilter(string $start, string $end): Closure
    {
        return function (Builder $query) use ($start, $end) {
            return $query
                ->whereNotNull('lead_state_id')
                ->whereBetween('created_at', [$start, $end]);
        };
    }

    protected function redirectWithFilter($filterCallback, string $label): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        $base = $this->getBaseFilter($start, $end);

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $filterCallback($base($query)),
            $label
        )->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }
}
