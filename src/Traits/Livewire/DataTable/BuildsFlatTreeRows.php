<?php

namespace FluxErp\Traits\Livewire\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait BuildsFlatTreeRows
{
    protected function buildFlatTreeRows(Builder $query): array
    {
        $rootIds = $query->pluck($this->modelTable . '.' . $this->modelKeyName);

        $records = $this->prepareFamilyTreeQuery(
            resolve_static($this->getModel(), 'familyTree')->whereKey($rootIds)
        )->get();

        $tree = to_flat_tree($records->toArray());
        $modelsById = $this->collectModelsById($records);

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

    protected function prepareFamilyTreeQuery(Builder $query): Builder
    {
        return $query;
    }
}
