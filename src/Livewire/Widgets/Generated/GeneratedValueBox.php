<?php

namespace FluxErp\Livewire\Widgets\Generated;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Support\Widgets\ValueBox;
use FluxErp\Traits\Livewire\Widget\HasGeneratedWidgetConfig;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;

class GeneratedValueBox extends ValueBox implements HasWidgetOptions
{
    use HasGeneratedWidgetConfig, IsTimeFrameAwareWidget {
        HasGeneratedWidgetConfig::dashboardComponent insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getCategory insteadof IsTimeFrameAwareWidget;
        HasGeneratedWidgetConfig::getLabel insteadof IsTimeFrameAwareWidget;
    }

    public function render(): View
    {
        return $this->renderWithErrorCheck(parent::render());
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateSum();
    }

    #[Renderless]
    public function calculateSum(): void
    {
        $query = $this->buildFilteredQuery();

        if (is_null($query)) {
            return;
        }

        $aggregate = $this->getAggregate();
        $column = $this->validateColumnName($this->getValueColumn());
        $dateColumn = $this->validateColumnName($this->getDateColumn());
        $isTimeframeAware = $this->isTimeframeAware() && $dateColumn;

        if ($aggregate !== 'count' && is_null($column)) {
            return;
        }

        if ($isTimeframeAware) {
            $previousQuery = $query->clone();
            $query->whereBetween($dateColumn, [$this->getStart(), $this->getEnd()]);
        }

        $rawSum = $aggregate === 'count'
            ? $query->count()
            : $query->{$aggregate}($column);

        $this->sum = $column && $aggregate !== 'count'
            ? strip_tags($this->formatColumnValue($column, $rawSum))
            : $rawSum;

        if ($isTimeframeAware) {
            $previousQuery->whereBetween($dateColumn, [$this->getStartPrevious(), $this->getEndPrevious()]);

            $rawPrevious = $aggregate === 'count'
                ? $previousQuery->count()
                : $previousQuery->{$aggregate}($column);

            $this->previousSum = $column && $aggregate !== 'count'
                ? strip_tags($this->formatColumnValue($column, $rawPrevious))
                : $rawPrevious;

            $this->growthRate = (float) $rawPrevious !== 0.0
                ? round((((float) $rawSum - (float) $rawPrevious) / abs((float) $rawPrevious)) * 100, 2)
                : null;
        }
    }

    public function options(): array
    {
        return [];
    }

    protected function icon(): string
    {
        return 'calculator';
    }
}
