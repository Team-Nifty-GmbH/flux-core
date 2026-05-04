<?php

namespace FluxErp\Livewire\Widgets\Generated;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Support\Widgets\Charts\BarChart;
use FluxErp\Traits\Livewire\Widget\HasGeneratedWidgetConfig;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Contracts\View\View;
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

    public function render(): View
    {
        return $this->renderWithErrorCheck(parent::render());
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

        $aggregateExpression = match ($aggregate) {
            'sum' => "SUM(`{$valueColumn}`) as aggregate_value",
            'avg' => "AVG(`{$valueColumn}`) as aggregate_value",
            'min' => "MIN(`{$valueColumn}`) as aggregate_value",
            'max' => "MAX(`{$valueColumn}`) as aggregate_value",
            default => 'COUNT(*) as aggregate_value',
        };

        $results = $query
            ->reorder()
            ->select($groupColumn)
            ->selectRaw($aggregateExpression)
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
