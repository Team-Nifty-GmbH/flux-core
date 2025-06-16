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
        $colors = [
            'lime-400',
            'sky-400',
            'violet-400',
            'cyan-400',
            'blue-400',
            'purple-400',
            'green-400',
            'indigo-400',
            'fuchsia-400',
            'emerald-400',
            'pink-400',
            'teal-400',
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
            ->get(['name', 'color', 'id']);

        $this->series = $leadCounts
            ->map(function ($user) use (&$colors) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'color' => $user->color ?? array_shift($colors),
                    'data' => [$user->total],
                ];
            })
            ->sortByDesc(fn (array $item): int => data_get($item, 'data.0'))
            ->take(10)
            ->values()
            ->all();

        $this->yaxis = [
            'labels' => ['show' => false],
        ];

        $this->actions = $this->options();
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
        return collect($this->series)
            ->map(fn (array $data) => [
                'label' => data_get($data, 'name'),
                'method' => 'show',
                'params' => [
                    'id' => data_get($data, 'id'),
                    'name' => data_get($data, 'name'),
                ],
            ])
            ->toArray();
    }

    #[Renderless]
    public function show(array $params): void
    {
        $salesRepresentativeId = data_get($params, 'id');
        $salesRepresentativeName = data_get($params, 'name');

        $startCarbon = $this->getStart();
        $endCarbon = $this->getEnd();

        $start = $startCarbon->toDateString();
        $end = $endCarbon->toDateString();

        $localizedStart = $startCarbon->translatedFormat('j. F Y');
        $localizedEnd = $endCarbon->translatedFormat('j. F Y');

        SessionFilter::make(
            Livewire::new(resolve_static(LeadList::class, 'class'))->getCacheKey(),
            fn (Builder $query) => $query
                ->where('user_id', $salesRepresentativeId)
                ->whereBetween('end', [$start, $end])
                ->whereHas('leadState', fn (Builder $q) => $q->where('is_won', true)),
            __('Won leads by :user', ['user' => $salesRepresentativeName]) . ' ' .
            __('between :start and :end', ['start' => $localizedStart, 'end' => $localizedEnd]),
        )
            ->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }
}
