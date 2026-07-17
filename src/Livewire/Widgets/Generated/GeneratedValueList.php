<?php

namespace FluxErp\Livewire\Widgets\Generated;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Support\Widgets\ValueList;
use FluxErp\Traits\Livewire\Widget\HasGeneratedWidgetConfig;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Renderless;

class GeneratedValueList extends ValueList implements HasWidgetOptions
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
        $this->skipRender();
        $this->calculateList();
    }

    #[Renderless]
    public function calculateList(): void
    {
        $query = $this->buildFilteredQuery();

        if (is_null($query)) {
            return;
        }

        $columns = array_values(array_filter(
            $this->getConfigValue('columns', []),
            fn (mixed $column) => is_string($column) && ! is_null($this->validateColumnName($column))
        ));
        $sortColumn = $this->getConfigValue('sort_column');
        $sortDirection = $this->getConfigValue('sort_direction', 'desc');
        $limit = max(1, min(100, (int) $this->getConfigValue('limit', $this->limit)));
        $dateColumn = $this->validateColumnName($this->getDateColumn());

        if ($this->isTimeframeAware() && $dateColumn) {
            $query->whereBetween($dateColumn, [$this->getStart(), $this->getEnd()]);
        }

        $model = $query->getModel();

        $query->select(array_merge([$model->getKeyName()], $columns));

        $sortColumn = $this->validateColumnName($sortColumn);

        if ($sortColumn && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $results = $query->limit($limit)->get();

        $hasColumns = count($columns) > 0;
        $lastColumn = last($columns);

        $this->items = $results->map(fn (Model $item) => [
            'label' => $hasColumns ? strip_tags($this->formatColumnValue($columns[0], $item->{$columns[0]})) : '',
            'subLabel' => count($columns) > 2 ? strip_tags($this->formatColumnValue($columns[1], $item->{$columns[1]})) : null,
            'value' => $hasColumns ? strip_tags($this->formatColumnValue($lastColumn, $item->{$lastColumn})) : '',
            'growthRate' => null,
        ])->toArray();
    }

    public function options(): array
    {
        return [];
    }
}
