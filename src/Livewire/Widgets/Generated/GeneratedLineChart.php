<?php

namespace FluxErp\Livewire\Widgets\Generated;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Support\Widgets\Charts\LineChart;
use FluxErp\Traits\Livewire\Widget\HasGeneratedWidgetConfig;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Renderless;

class GeneratedLineChart extends LineChart implements HasWidgetOptions
{
    use HasGeneratedWidgetConfig, IsTimeFrameAwareWidget {
        HasGeneratedWidgetConfig::dashboardComponent insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getCategory insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getLabel insteadof IsTimeFrameAwareWidget;
    }

    public function render(): View|Factory
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
        $dateColumn = $this->validateColumnName($this->getDateColumn() ?? 'created_at');

        if (is_null($dateColumn)) {
            return;
        }

        if ($aggregate !== 'count' && is_null($valueColumn)) {
            return;
        }

        if ($this->isTimeframeAware() && $dateColumn) {
            $query->whereBetween($dateColumn, [$this->getStart(), $this->getEnd()]);
        }

        $unit = $this->getUnit() ?? 'month';
        $dateFormat = match ($unit) {
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m',
        };

        $aggregateExpression = match ($aggregate) {
            'sum' => "SUM({$valueColumn})",
            'avg' => "AVG({$valueColumn})",
            'min' => "MIN({$valueColumn})",
            'max' => "MAX({$valueColumn})",
            default => 'COUNT(*)',
        };

        $results = $query
            ->reorder()
            ->select(DB::raw("DATE_FORMAT({$dateColumn}, '{$dateFormat}') as period, {$aggregateExpression} as aggregate_value"))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $this->series = [
            [
                'name' => $this->title() ?? static::getLabel(),
                'data' => $results->pluck('aggregate_value')->map(fn ($value) => round((float) $value, 2))->toArray(),
            ],
        ];

        $this->xaxis = [
            'categories' => $results->pluck('period')->toArray(),
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
