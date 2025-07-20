<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Lead;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Renderless;

class ActiveLeadsByClosingProbability extends BarChart
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
            'horizontal' => false,
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
        $granularity = 25;

        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        $activeLeads = resolve_static(Lead::class, 'query')
            ->whereBetween('end', [$start, $end])
            ->where('probability_percentage', '<', 1)
            ->where('probability_percentage', '>', 0)
            ->whereHas('leadState', function (Builder $query): void {
                $query
                    ->where('is_won', false)
                    ->where('is_lost', false);
            })
            ->get();

        $bins = $activeLeads->groupBy(function ($lead) use ($granularity) {
            return floor($lead->probability_percentage * 100 / $granularity) * $granularity;
        })->sortKeys();

        $this->series = [
            [
                'color' => '#2E93fA',
                'data' => [],
            ],
        ];

        $this->xaxis['categories'] = [];

        foreach ($bins as $lower => $leadsInBin) {
            $upper = $lower + $granularity;
            $label = "{$lower}%–{$upper}%";

            $this->series[0]['data'][] = $leadsInBin->count();
            $this->xaxis['categories'][] = $label;
        }
    }
}
