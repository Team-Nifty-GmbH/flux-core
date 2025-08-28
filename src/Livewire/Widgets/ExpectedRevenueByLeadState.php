<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Lead;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\MoneyChartFormattingTrait;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class ExpectedRevenueByLeadState extends BarChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, MoneyChartFormattingTrait, Widgetable;

    public ?array $chart = [
        'type' => 'bar',
    ];

    public ?array $dataLabels = [
        'enabled' => true,
    ];

    public ?array $plotOptions = [
        'bar' => [
            'horizontal' => true,
            'endingShape' => 'rounded',
            'columnWidth' => '75%',
        ],
    ];

    public bool $showTotals = false;

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

    #[Renderless]
    public function calculateChart(): void
    {
        $this->series = resolve_static(Lead::class, 'query')
            ->whereNotNull('lead_state_id')
            ->whereHas('leadState', function (Builder $query): void {
                $query
                    ->where('is_won', false)
                    ->where('is_lost', false);
            })
            ->where('probability_percentage', '<', 1)
            ->where('probability_percentage', '>', 0)
            ->whereNotNull('weighted_revenue')
            ->whereBetween(
                'created_at',
                [
                    $this->getStart()->toDateTimeString(),
                    $this->getEnd()->toDateTimeString(),
                ]
            )
            ->groupBy('lead_state_id')
            ->with('leadState:id,name,color')
            ->selectRaw('lead_state_id, SUM(weighted_revenue) as total_revenue')
            ->get()
            ->map(fn (Lead $lead): array => [
                'id' => $lead->lead_state_id,
                'name' => $lead->leadState->name,
                'color' => $lead->leadState->color,
                'data' => [$lead->total_revenue],
            ])
            ->toArray();

        $this->yaxis = [
            'labels' => ['show' => false],
        ];
    }

    #[Renderless]
    public function options(): array
    {
        return array_map(
            fn (array $data) => [
                'label' => data_get($data, 'name'),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                    'name' => data_get($data, 'name'),
                ],
            ],
            $this->series
        );
    }

    #[Renderless]
    public function show(array $params): void
    {
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('lead_state_id', data_get($params, 'id'))
                ->whereHas(
                    'leadState',
                    fn (Builder $query) => $query
                        ->where('is_won', false)
                        ->where('is_lost', false)
                )
                ->where('probability_percentage', '<', 1)
                ->where('probability_percentage', '>', 0)
                ->whereNotNull('weighted_revenue')
                ->whereBetween('created_at', [$start, $end]),
            __('Leads with state :lead-state', ['lead-state' => data_get($params, 'name')]) . ' ' .
            __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }
}
