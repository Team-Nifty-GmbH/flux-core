<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class CategoryList extends DataTable
{
    protected string $model = Category::class;

    public array $enabledCols = [
        'name',
        'model_type',
        'is_active',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id')->with('children');
    }

    public function getResultFromQuery(Builder $query): array
    {
        $tree = to_flat_tree($query->get()->toArray());

        $returnKeys = array_merge($this->getReturnKeys(), ['depth']);

        foreach ($tree as &$item) {
            $item = Arr::only(Arr::dot($item), $returnKeys);
            $item['indentation'] = '';

            if ($item['depth'] > 0) {
                $indent = $item['depth'] * 20;
                $item['indentation'] = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
            }
        }

        return $tree;
    }

    public function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }
}
