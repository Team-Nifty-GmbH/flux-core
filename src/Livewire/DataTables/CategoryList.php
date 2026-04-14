<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class CategoryList extends BaseDataTable
{
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
        // Get filtered root IDs from the query (respects user filters),
        // then load the tree with recursive children via familyTree()
        $rootIds = $query->pluck($this->modelTable . '.' . $this->modelKeyName);

        $categories = resolve_static(Category::class, 'familyTree')
            ->whereKey($rootIds)
            ->get();

        $tree = to_flat_tree($categories->toArray());

        // Collect ALL models recursively (parents + all nested children)
        $allModels = collect();
        $collectRecursive = function ($items) use (&$collectRecursive, &$allModels): void {
            foreach ($items as $item) {
                $allModels->push($item);

                if ($item->relationLoaded('children')) {
                    $collectRecursive($item->children);
                }
            }
        };

        $collectRecursive($categories);
        $modelsById = $allModels->keyBy(fn ($m) => $m->getKey());

        $data = [];
        foreach ($tree as $item) {
            $model = $modelsById->get($item['id']);

            if ($model) {
                $row = $this->itemToArray($model);
            } else {
                $row = Arr::only(Arr::dot($item), $this->getReturnKeys());
            }

            $row['depth'] = $item['depth'];
            $row['indentation'] = '';

            if ($item['depth'] > 0) {
                $indent = $item['depth'] * 20;
                $row['indentation'] = '<div class="shrink-0" style="min-width:' . $indent . 'px"></div>';
            }

            $data[] = $row;
        }

        return [
            'data' => $data,
            'total' => count($data),
        ];
    }
}
