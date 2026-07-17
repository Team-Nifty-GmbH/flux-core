<?php

namespace FluxErp\Livewire\Widgets\Generated;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Traits\Livewire\Widget\HasGeneratedWidgetConfig;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;

class GeneratedLineChart extends LineChart implements HasWidgetOptions
{
    use HasGeneratedWidgetConfig, IsTimeFrameAwareWidget {
        HasGeneratedWidgetConfig::dashboardComponent insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getCategory insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getLabel insteadof IsTimeFrameAwareWidget;
    }

    public string $valueFormatterType = 'float';

    public string $xAxisFormatterType = 'date';

    public function render(): View
    {
        return $this->renderWithErrorCheck(parent::render());
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->skipRender();
        $this->calculateChart();
        $this->updateData();
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
        $configuredDateColumn = $this->getDateColumn();

        if (is_null($configuredDateColumn)) {
            $model = $query->getModel();
            $configuredDateColumn = $model->usesTimestamps() ? $model->getCreatedAtColumn() : null;
        }

        $dateColumn = $this->validateColumnName($configuredDateColumn);

        if (is_null($dateColumn)) {
            return;
        }

        if ($aggregate !== 'count' && is_null($valueColumn)) {
            return;
        }

        if ($this->isTimeframeAware()) {
            $query->whereBetween($dateColumn, [$this->getStart(), $this->getEnd()]);
        }

        $unit = $this->getConfigValue('time_grouping') ?? $this->getUnit() ?? 'month';
        $dateFormat = match ($unit) {
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m',
        };

        $quotedDateColumn = '`' . str_replace('.', '`.`', $dateColumn) . '`';

        $results = $query
            ->reorder()
            ->selectRaw(
                "DATE_FORMAT({$quotedDateColumn}, '{$dateFormat}') as period, "
                . $this->buildAggregateExpression($aggregate, $valueColumn)
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $this->series = [
            [
                'name' => $this->title() ?? static::getLabel(),
                'data' => $results->pluck('aggregate_value')
                    ->map(fn (mixed $value) => round((float) $value, 2))
                    ->toArray(),
            ],
        ];

        $this->xaxis = [
            'categories' => $results->pluck('period')->toArray(),
        ];

        $this->valueFormatterType = $this->resolveJsFormatterName($valueColumn);
        $this->showTotals = (bool) $this->getConfigValue('show_totals', true);

        $type = $this->getConfigValue('type', 'line_chart');
        $this->chart = ['type' => $type === 'area_chart' ? 'area' : 'line'];

        $curveStyle = $this->getConfigValue('curve_style', 'smooth');
        $this->stroke = [
            'show' => true,
            'width' => 4,
            'curve' => $curveStyle,
        ];
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
