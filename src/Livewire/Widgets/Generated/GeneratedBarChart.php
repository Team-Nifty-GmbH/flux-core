<?php

namespace FluxErp\Livewire\Widgets\Generated;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Traits\Livewire\Widget\HasGeneratedWidgetConfig;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Renderless;

class GeneratedBarChart extends BarChart implements HasWidgetOptions
{
    use HasGeneratedWidgetConfig, IsTimeFrameAwareWidget {
        HasGeneratedWidgetConfig::dashboardComponent insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getCategory insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getLabel insteadof IsTimeFrameAwareWidget;
    }

    public string $valueFormatterType = 'float';

    public string $xAxisFormatterType = 'float';

    public function render(): View|Factory
    {
        return $this->renderWithErrorCheck(parent::render());
    }

    public function boot(): void
    {
        // Don't skip render — parent Chart::boot() skips when series is set,
        // but we need the initial render for the ApexCharts JS to initialize
    }

    #[Renderless]
    public function calculateChart(): void
    {
        $query = $this->buildFilteredQuery();

        if (is_null($query)) {
            return;
        }

        $aggregate = $this->getAggregate();
        $valueColumn = $this->validateColumnName($this->getValueColumn());
        $groupColumn = $this->validateColumnName($this->getGroupColumn());
        $dateColumn = $this->validateColumnName($this->getDateColumn());

        if (is_null($groupColumn)) {
            return;
        }

        if ($aggregate !== 'count' && is_null($valueColumn)) {
            return;
        }

        if ($this->isTimeframeAware() && $dateColumn) {
            $query->whereBetween($dateColumn, [$this->getStart(), $this->getEnd()]);
        }

        $aggregateExpression = $aggregate === 'count'
            ? DB::raw('COUNT(*) as aggregate_value')
            : DB::raw("{$aggregate}(`{$valueColumn}`) as aggregate_value");

        $results = $query
            ->reorder()
            ->select([$groupColumn, $aggregateExpression])
            ->groupBy($groupColumn)
            ->orderByDesc('aggregate_value')
            ->get();

        $type = $this->getConfigValue('type', 'bar_chart');
        $isPie = $type === 'pie_chart';

        if ($isPie) {
            $pieStyle = $this->getConfigValue('pie_style', 'pie');
            $this->chart = ['type' => $pieStyle];
            $this->series = $results->pluck('aggregate_value')->map(fn ($v) => round((float) $v, 2))->toArray();
            $this->labels = $results->pluck($groupColumn)
                ->map(fn ($v) => strip_tags($this->formatColumnValue($groupColumn, $v)))
                ->toArray();
            $this->showTotals = false;
        } else {
            $this->series = [
                [
                    'name' => $this->title() ?? static::getLabel(),
                    'data' => $results->pluck('aggregate_value')->map(fn ($v) => round((float) $v, 2))->toArray(),
                ],
            ];

            $this->xaxis = [
                'categories' => $results->pluck($groupColumn)->toArray(),
            ];

            $this->showTotals = (bool) $this->getConfigValue('show_totals', true);

            if ($this->getConfigValue('horizontal_bars', false)) {
                $this->plotOptions = [
                    'bar' => [
                        'horizontal' => true,
                        'endingShape' => 'rounded',
                        'columnWidth' => '75%',
                    ],
                ];
            }
        }

        $this->valueFormatterType = $this->resolveJsFormatterName($valueColumn);
        $this->xAxisFormatterType = $this->resolveJsFormatterName($groupColumn);
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->skipRender();
        $this->calculateChart();
        $this->updateData();
    }

    public function options(): array
    {
        return [];
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();
        unset($options['config'], $options['configError'], $options['configErrorMessage']);

        return $options;
    }
}
