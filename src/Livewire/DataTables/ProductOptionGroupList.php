<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductOptionGroup;

class ProductOptionGroupList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'product_options.name',
    ];

    protected string $model = ProductOptionGroup::class;

    protected function itemToArray($item): array
    {
        $item->productOptions->each(fn ($productOption) => $productOption->localize());

        return parent::itemToArray($item);
    }
}
