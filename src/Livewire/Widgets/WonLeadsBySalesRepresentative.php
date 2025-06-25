<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Js;
use Livewire\Attributes\Renderless;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

class WonLeadsBySalesRepresentative extends BarChart implements HasWidgetOptions
{
    use IsTimeFrameAwareWidget, Widgetable;

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
        // Default colors of apexcharts
        $colors = [
            '#2E93fA',
            '#66DA26',
            '#546E7A',
            '#E91E63',
            '#FF9800',
        ];

        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        $leadCounts = resolve_static(User::class, 'query')
            ->withCount([
                'leads as total' => function (Builder $query) use ($start, $end): void {
                    $query->whereHas('leadState', function (Builder $query): void {
                        $query->where('is_won', true);
                    })
                        ->whereBetween('end', [$start, $end]);
                },
            ])
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->get();

        $i = 0;
        $this->series = $leadCounts
            ->map(function ($user) use (&$i, $colors): array {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'color' => $user->color ?? $colors[$i++ % count($colors)],
                    'data' => [$user->total],
                ];
            })
            ->take(10)
            ->values()
            ->all();

        $this->yaxis = [
            'labels' => ['show' => false],
        ];
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return <<<'JS'
            return opts.w.config.series[opts.seriesIndex].name + ': ' + val;
        JS;
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
                ->where('user_id', data_get($params, 'id'))
                ->whereBetween('end', [$start, $end])
                ->whereHas('leadState', fn (Builder $q) => $q->where('is_won', true)),
            __('Won leads by :user', ['user' => data_get($params, 'name')]) . ' ' .
            __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }
}
