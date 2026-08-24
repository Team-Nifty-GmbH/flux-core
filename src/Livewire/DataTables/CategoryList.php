<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Category;
use FluxErp\Traits\Livewire\DataTable\BuildsFlatTreeRows;
use Illuminate\Database\Eloquent\Builder;

class CategoryList extends BaseDataTable
{
    use BuildsFlatTreeRows;

    public array $enabledCols = [
        'name',
        'model_type',
        'is_active',
    ];

    protected string $model = Category::class;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id');
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }

    protected function getResultFromQuery(Builder $query): array
    {
        return $this->buildFlatTreeRows($query);
    }

    protected function prepareFamilyTreeQuery(Builder $query): Builder
    {
        return $query->ordered();
    }
}
