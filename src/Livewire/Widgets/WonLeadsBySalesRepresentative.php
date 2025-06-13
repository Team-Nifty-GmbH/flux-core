<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use Livewire\Attributes\Js;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

class WonLeadsBySalesRepresentative extends BarChart
{
    use IsTimeFrameAwareWidget;

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

    public function mount(): void
    {
        $this->userId = $this->userId ?? auth()->id();

        parent::mount();
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

        $leadCounts = resolve_static(User::class, 'query')
            ->withCount([
                'leads as total' => function ($query): void {
                    $query->whereHas('leadState', function ($q): void {
                        $q->where('is_won', 1);
                    });
                },
            ])
            ->limit(10)
            ->get(['name', 'color'])
            ->filter(fn ($user) => $user->total > 0);

        $data = $leadCounts
            ->map(function ($user) use (&$colors) {
                return [
                    'name' => $user->name,
                    'color' => $user->color ?: array_shift($colors),
                    'data' => [$user->total],
                ];
            })
            ->sortByDesc(fn ($item) => $item['data'][0])
            ->values()
            ->all();

        $this->xaxis = [
            'categories' => [''], // necessary to remove y-label
        ];

        $this->series = $data;
    }

    #[Js]
    public function dataLabelsFormatter(): string
    {
        return <<<'JS'
        return opts.w.config.series[opts.seriesIndex].name + ': ' + val;
    JS;
    }

    public function showTitle(): bool
    {
        return true;
    }
}
