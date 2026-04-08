<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Model;

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

    protected function augmentItemArray(array &$itemArray, Model $item): void
    {
        $itemArray['product_image'] = $item->getAvatarUrl();
    }
}
