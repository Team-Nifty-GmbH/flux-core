<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\StorageArea;
use FluxErp\Traits\Livewire\DataTable\BuildsFlatTreeRows;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class StorageAreaList extends BaseDataTable
{
    use BuildsFlatTreeRows;

    public array $columnLabels = [
        'parent.code' => 'Parent',
    ];

    public array $enabledCols = [
        'code',
        'name',
        'storage_area_type_enum',
        'warehouse.name',
        'parent.code',
        'is_storage_location',
        'is_active',
        'sort_number',
    ];

    protected string $model = StorageArea::class;

    protected function getLeftAppends(): array
    {
        return [
            'code' => 'indentation',
        ];
    }

    protected function getResultFromQuery(Builder $query): array
    {
        return $this->buildFlatTreeRows($query);
    }

    protected function loadFamilyTreeRelations(EloquentCollection $records): void
    {
        $records->load(['warehouse:id,name', 'parent:id,code']);
    }

    protected function prepareFamilyTreeQuery(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_number')
            ->orderBy('code');
    }
}
