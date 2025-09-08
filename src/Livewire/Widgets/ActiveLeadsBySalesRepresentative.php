<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Enums\ChartColorEnum;
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

class ActiveLeadsBySalesRepresentative extends BarChart implements HasWidgetOptions
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

    public ?array $yaxis = [
        'labels' => ['show' => false],
    ];

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
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        $leadCounts = resolve_static(User::class, 'query')
            ->withCount([
                'leads as total' => function (Builder $query) use ($start, $end): void {
                    $query->whereHas('leadState', function (Builder $query): void {
                        $query
                            ->where('is_won', false)
                            ->where('is_lost', false);
                    })
                        ->whereBetween('end', [$start, $end]);
                },
            ])
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->get();

        $this->series = $leadCounts
            ->map(function (User $user): array {
                return [
                    'id' => $user->getKey(),
                    'name' => $user->getLabel(),
                    'color' => $user->color ?: ChartColorEnum::forKey($user->getKey())->value,
                    'data' => [$user->total],
                ];
            })
            ->take(25)
            ->values()
            ->all();
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
                ->whereValueBetween(now(), ['start', 'end'])
                ->whereHas(
                    'leadState',
                    fn (Builder $query) => $query
                        ->where('is_won', false)
                        ->where('is_lost', false)
                ),
            __('Active leads by :user', ['user' => data_get($params, 'name')]) . ' ' .
            __('between :start and :end', ['start' => $start, 'end' => $end]),
        )
            ->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }
}
