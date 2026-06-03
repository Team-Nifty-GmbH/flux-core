<?php

namespace FluxErp\Traits\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Spatie\ModelInfo\Attributes\Attribute;
use Spatie\ModelInfo\Attributes\AttributeFinder;
use TeamNiftyGmbH\DataTable\DataTable;

trait HasWidgetGeneration
{
    public static function getWidgetModel(): string
    {
        return app(static::class)->getModel();
    }

    public function bootHasWidgetGeneration(): void
    {
        if (! $this instanceof DataTable) {
            throw new InvalidArgumentException('This trait can only be used in a DataTable');
        }
    }

    public function buildWidgetQuery(array $userFilters): Builder
    {
        $originalUserFilters = $this->userFilters;
        $originalSearch = $this->search;
        $originalSessionFilter = $this->sessionFilter;

        if (! $this->modelKeyName || ! $this->modelTable) {
            $model = app($this->getModel());
            $this->modelKeyName = $this->modelKeyName ?: $model->getKeyName();
            $this->modelTable = $this->modelTable ?: $model->getTable();
        }

        try {
            $this->userFilters = $userFilters;
            $this->search = '';
            $this->sessionFilter = [];

            $query = $this->buildSearch(unpaginated: true);
        } finally {
            $this->userFilters = $originalUserFilters;
            $this->search = $originalSearch;
            $this->sessionFilter = $originalSessionFilter;
        }

        return $query;
    }

    public function openWidgetWizard(): void
    {
        session()->put('widget-wizard-filters', $this->userFilters);

        $this->redirectRoute('widgets.create', ['datatable' => static::class], navigate: true);
    }

    public function buildAvailableColumns(): array
    {
        $numericTypes = ['integer', 'bigint', 'smallint', 'tinyint', 'mediumint', 'decimal', 'float', 'double'];
        $dateTypes = ['date', 'datetime', 'timestamp'];

        return AttributeFinder::forModel($this->getModel())
            ->filter(fn (Attribute $attribute) => ! $attribute->virtual && ! $attribute->appended)
            ->map(function (Attribute $attribute) use ($numericTypes, $dateTypes) {
                $dbType = strtolower($attribute->type ?? '');
                $cast = strtolower($attribute->cast ?? '');

                $isNumeric = collect($numericTypes)->contains(fn (string $t) => str_starts_with($dbType, $t));
                $isDate = collect($dateTypes)->contains(fn (string $t) => str_starts_with($dbType, $t));
                $isBoolean = $attribute->phpType === 'bool' || in_array($cast, ['bool', 'boolean'], true);
                $isForeignKey = $attribute->name === 'id' || str_ends_with($attribute->name, '_id');

                if ($isNumeric && ! $isBoolean && ! $isForeignKey) {
                    $type = 'numeric';
                } elseif ($isDate) {
                    $type = 'date';
                } else {
                    $type = 'string';
                }

                return [
                    'name' => $attribute->name,
                    'label' => __(str($attribute->name)->headline()->toString()),
                    'type' => $type,
                ];
            })
            ->values()
            ->all();
    }

    public function getSidebarTabs(): array
    {
        $tabs = parent::getSidebarTabs();

        $widgetTab = [
            'id' => 'save-as-widget',
            'label' => __('Widget'),
            'view' => 'flux::livewire.datatables.sidebar-widget-tab',
        ];

        $insertAt = 0;

        foreach ($tabs as $index => $tab) {
            if (data_get($tab, 'id') === 'edit-filters') {
                $insertAt = $index + 1;

                break;
            }
        }

        array_splice($tabs, $insertAt, 0, [$widgetTab]);

        return $tabs;
    }
}
