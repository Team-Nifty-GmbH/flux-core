<?php

namespace FluxErp\Traits\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait BuildsFlatTreeRows
{
    protected function buildFlatTreeRows(Builder $query): array
    {
        // A match may sit anywhere in the tree, so every matched record is resolved to the root of
        // its family before the tree is fetched. The parent keys of the whole table are read once
        // and walked in memory instead of asking the database for the ancestors of every record.
        // ponytail: reads id and parent of every row, fine for trees of a few thousand nodes
        $parentKeys = resolve_static($this->getModel(), 'query')
            ->pluck($this->familyParentKey(), $this->modelKeyName);

        $rootIds = $query->pluck($this->modelTable . '.' . $this->modelKeyName)
            ->map(function (int|string $id) use ($parentKeys): int|string {
                $depth = 0;

                // The depth limit only guards against a cycle in broken data.
                while (($parentId = $parentKeys->get($id)) && $depth++ < $parentKeys->count()) {
                    $id = $parentId;
                }

                return $id;
            })
            ->unique()
            ->values();

        $records = $this->prepareFamilyTreeQuery(
            resolve_static($this->getModel(), 'familyTree')->whereKey($rootIds)
        )->get();

        $modelsById = $this->collectModelsById($records);

        $this->loadFamilyTreeRelations(
            EloquentCollection::make(array_values($modelsById))
        );

        $tree = to_flat_tree($records->toArray());

        $data = [];
        foreach ($tree as $item) {
            $model = $modelsById[$item['id']] ?? null;

            $row = $model
                ? $this->itemToArray($model)
                : Arr::only(Arr::dot($item), $this->getReturnKeys());

            $row['depth'] = $item['depth'];
            $row['indentation'] = $item['depth'] > 0
                ? '<div class="shrink-0" style="min-width:' . $item['depth'] * 20 . 'px"></div>'
                : '';

            $data[] = $row;
        }

        return [
            'data' => $data,
            'total' => count($data),
        ];
    }

    protected function collectModelsById(Collection $items, array &$result = []): array
    {
        foreach ($items as $item) {
            $result[$item->getKey()] = $item;

            if ($item->relationLoaded('children')) {
                $this->collectModelsById($item->children, $result);
            }
        }

        return $result;
    }

    protected function familyParentKey(): string
    {
        return 'parent_id';
    }

    /**
     * Runs once the whole tree is in memory and the temporary family-tree scope is gone, so a
     * relation may be loaded here without the scope forcing `children` onto its sub-query.
     */
    protected function loadFamilyTreeRelations(EloquentCollection $records): void {}

    protected function prepareFamilyTreeQuery(Builder $query): Builder
    {
        return $query;
    }
}
