<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\WarehouseBin;
use FluxErp\Traits\Livewire\DataTable\BuildsFlatTreeRows;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class WarehouseBinList extends BaseDataTable
{
    use BuildsFlatTreeRows;

    public array $columnLabels = [
        'parent.code' => 'Parent',
    ];

    public array $enabledCols = [
        'code',
        'name',
        'warehouse_bin_type_enum',
        'warehouse.name',
        'parent.code',
        'is_storage_location',
        'is_active',
        'sort_order',
    ];

    protected string $model = WarehouseBin::class;

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
            ->orderBy('sort_order')
            ->orderBy('code');
    }
}
