<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Product;

class ProductList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'product_number',
        'product_type',
        'is_active',
        'categories.name',
    ];

    public array $formatters = [
        'product_image' => 'image',
    ];

    protected string $model = Product::class;

    protected function getLeftAppends(): array
    {
        return [
            'name' => [
                'product_image',
            ],
        ];
    }

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['product_image'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
