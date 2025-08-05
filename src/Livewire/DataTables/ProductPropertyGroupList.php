<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductPropertyGroup;

class ProductPropertyGroupList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'product_properties.name',
    ];

    protected string $model = ProductPropertyGroup::class;

    protected function itemToArray($item): array
    {
        $item->productProperties->each(fn ($productProperty) => $productProperty->localize());

        return parent::itemToArray($item);
    }
}
