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
    public function calculateSum(): void
    {
        $query = $this->buildFilteredQuery();

        if (is_null($query)) {
            return;
        }

        $aggregate = $this->getAggregate();
        $column = $this->getValueColumn();
        $dateColumn = $this->getDateColumn();

        if ($this->isTimeframeAware() && $dateColumn) {
            $query->whereBetween($dateColumn, [$this->getStart(), $this->getEnd()]);
        }

        $rawSum = $aggregate === 'count'
            ? $query->count()
            : $query->{$aggregate}($column);

        $this->sum = $column && $aggregate !== 'count'
            ? strip_tags($this->formatColumnValue($column, $rawSum))
            : $rawSum;

        if ($this->isTimeframeAware() && $dateColumn) {
            $previousQuery = $this->buildFilteredQuery();

            if (is_null($previousQuery)) {
                return;
            }

            $previousQuery->whereBetween($dateColumn, [$this->getStartPrevious(), $this->getEndPrevious()]);

            $rawPrevious = $aggregate === 'count'
                ? $previousQuery->count()
                : $previousQuery->{$aggregate}($column);

            $this->previousSum = $column && $aggregate !== 'count'
                ? strip_tags($this->formatColumnValue($column, $rawPrevious))
                : $rawPrevious;

            $this->growthRate = $rawPrevious != 0
                ? round((($rawSum - $rawPrevious) / abs($rawPrevious)) * 100, 2)
                : null;
        }
    }

    #[Renderless]
    public function calculateByTimeFrame(): void
    {
        $this->calculateSum();
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
