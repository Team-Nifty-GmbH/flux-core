<?php

namespace FluxErp\Livewire\Widgets\Generated;

use FluxErp\Contracts\HasWidgetOptions;
use FluxErp\Livewire\Support\Widgets\ValueList;
use FluxErp\Traits\Livewire\Widget\HasGeneratedWidgetConfig;
use FluxErp\Traits\Livewire\Widget\IsTimeFrameAwareWidget;
use Illuminate\Contracts\View\View;
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

        $columns = $this->getConfigValue('columns', []);
        $sortColumn = $this->getConfigValue('sort_column');
        $sortDirection = $this->getConfigValue('sort_direction', 'desc');
        $limit = $this->getConfigValue('limit', $this->limit);
        $dateColumn = $this->getDateColumn();

        if ($this->isTimeframeAware() && $dateColumn) {
            $query->whereBetween($dateColumn, [$this->getStart(), $this->getEnd()]);
        }

        $model = $query->getModel();
        $selectColumns = array_merge([$model->getKeyName()], $columns);

        $query->select($selectColumns);

        $sortColumn = $this->validateColumnName($sortColumn);
        if ($sortColumn && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $results = $query->limit($limit)->get();

        $this->items = $results->map(function ($item) use ($columns) {
            $label = count($columns) > 0 ? (string) $item->{$columns[0]} : '';
            $value = count($columns) > 0 ? (string) $item->{$columns[count($columns) - 1]} : '';
            $subLabel = count($columns) > 2 ? (string) $item->{$columns[1]} : null;

            return [
                'label' => $label,
                'subLabel' => $subLabel,
                'value' => $value,
                'growthRate' => null,
            ];
        })->toArray();
    }

    #[Renderless]
    public function options(): array
    {
        return [];
    }
}
