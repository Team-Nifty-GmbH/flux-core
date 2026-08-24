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
        $key = $this->modelTable . '.' . $this->modelKeyName;
        $parentKey = $this->modelTable . '.' . $this->familyParentKey();

        // A match may sit anywhere in the tree, so every matched record is resolved to the root of
        // its family before the tree is fetched. Consumers that already restrict the query to roots
        // get their own keys back and pay nothing for it.
        $rootIds = $query->get([$key, $parentKey])
            ->map(fn ($record) => $record->familyRootKey())
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

    /**
     * Runs once the whole tree is in memory and the temporary family-tree scope is gone, so a
     * relation may be loaded here without the scope forcing `children` onto its sub-query.
     */
    protected function familyParentKey(): string
    {
        return 'parent_id';
    }

    protected function loadFamilyTreeRelations(EloquentCollection $records): void {}

    protected function prepareFamilyTreeQuery(Builder $query): Builder
    {
        return $query;
    }
}
