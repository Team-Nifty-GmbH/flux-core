<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Scopes\FamilyTreeScope;
use FluxErp\Models\WarehouseBin;
use FluxErp\Traits\Livewire\DataTable\BuildsFlatTreeRows;
use Illuminate\Database\Eloquent\Builder;

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

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id');
    }

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

    /**
     * `warehouse`/`parent` must be loaded at every depth, not just on the root rows: `FamilyTreeScope`
     * builds the `children` sub-queries lazily during `->get()`, after this method has already returned,
     * so a plain `with()` on the given `$query` would only ever reach the root. Registering the eager
     * loads as a temporary global scope makes them cascade the same way `children` does.
     *
     * `parent` strips `FamilyTreeScope` off its own sub-query on purpose: without that, loading a row's
     * `parent` would also eager-load that parent's `children` (which includes the row itself), which
     * loads its `parent` again, forever — an infinite parent<->children cycle that crashes the process.
     */
    protected function prepareFamilyTreeQuery(Builder $query): Builder
    {
        $eagerLoadRelations = fn (Builder $builder) => $builder->with([
            'warehouse:id,name',
            'parent' => fn ($parentQuery) => $parentQuery
                ->select(['id', 'code'])
                ->withoutGlobalScope(FamilyTreeScope::class),
        ]);

        resolve_static($this->getModel(), 'addGlobalScope', ['warehouseBinEagerLoad', $eagerLoadRelations]);

        return $eagerLoadRelations($query)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->afterQuery(fn () => resolve_static($this->getModel(), 'removeGlobalScopes', [['warehouseBinEagerLoad']]));
    }
}
