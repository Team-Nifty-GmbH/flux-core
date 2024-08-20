<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Product;

class ProductList extends BaseDataTable
{
    protected string $model = Product::class;

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

    protected function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['product_image'] = $item->getAvatarUrl();

        return $returnArray;
    }

    protected function getLeftAppends(): array
    {
        return [
            'name' => [
                'product_image',
            ],
        ];
    }
}
