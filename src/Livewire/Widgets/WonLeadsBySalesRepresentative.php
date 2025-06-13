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
use Livewire\Attributes\Locked;
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

    #[Locked]
    public ?int $userId = null;

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
                'leads as total' => function ($query) use ($start, $end): void {
                    $query->whereHas('leadState', function ($q): void {
                        $q->where('is_won', 1);
                    })
                        ->whereBetween('end', [$start, $end]);
                },
            ])
            ->get(['name', 'color', 'id'])
            ->filter(fn ($user) => $user->total > 0);

        $data = $leadCounts
            ->map(function ($user) use (&$colors) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'color' => $user->color ?: array_shift($colors),
                    'data' => [$user->total],
                ];
            })
            ->sortByDesc(fn ($item) => $item['data'][0])
            ->take(10)
            ->values()
            ->all();

        $this->series = $data;

        $this->xaxis = [
            'categories' => [''], // necessary to remove y-label
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
            ->map(fn ($data) => [
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
        $salesRepresentativeId = $params['id'];
        $salesRepresentativeName = $params['name'];

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
        )->store();

        $this->redirectRoute('sales.leads', navigate: true);
    }

    public function showTitle(): bool
    {
        return true;
    }
}
