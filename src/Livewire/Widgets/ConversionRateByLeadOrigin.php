<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Models\Lead;
use FluxErp\Models\RecordOrigin;
use FluxErp\Traits\Livewire\IsTimeFrameAwareWidget;
use FluxErp\Traits\Widgetable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Js;
use Livewire\Attributes\Renderless;

class ConversionRateByLeadOrigin extends BarChart
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
        $start = $this->getStart()->toDateString();
        $end = $this->getEnd()->toDateString();

        $LeadsWithWonOrLostLeadState = resolve_static(RecordOrigin::class, 'query')
            ->where('model_type', morph_alias(Lead::class))
            ->withCount([
                'leads as total' => function (Builder $query) use ($start, $end): void {
                    $query->whereHas('leadState', function (Builder $query): void {
                        $query
                            ->where('is_won', true)
                            ->orWhere('is_lost', true);
                    })
                        ->whereBetween('end', [$start, $end]);
                },
            ])
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $LeadsWithWonLeadState = resolve_static(RecordOrigin::class, 'query')
            ->where('model_type', morph_alias(Lead::class))
            ->withCount([
                'leads as total' => function (Builder $query) use ($start, $end): void {
                    $query->whereHas('leadState', function (Builder $query): void {
                        $query
                            ->where('is_won', true);
                    })
                        ->whereBetween('end', [$start, $end]);
                },
            ])
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $this->series = $LeadsWithWonOrLostLeadState
            ->map(function (Model $LeadWithWonOrLostLeadState) use ($LeadsWithWonLeadState): array {
                $conversionRate = round(bcdiv(
                    $LeadsWithWonLeadState->find($LeadWithWonOrLostLeadState->getKey())?->total ?? 0,
                    $LeadWithWonOrLostLeadState->total,
                    3
                ) * 100, 1);

                return [
                    'id' => $LeadWithWonOrLostLeadState->getKey(),
                    'name' => $LeadWithWonOrLostLeadState->name,
                    'color' => ChartColorEnum::forKey($LeadWithWonOrLostLeadState->getKey())->value,
                    'data' => [$conversionRate],
                ];
            })
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
            return opts.w.config.series[opts.seriesIndex].name + ': ' + val + '%';
        JS;
    }
}
