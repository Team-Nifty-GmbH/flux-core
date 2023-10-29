<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Product;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class ProductList extends DataTable
{
    protected string $model = Product::class;

    public array $enabledCols = [
        'id',
        'product_image',
        'product_number',
        'name',
    ];

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];

    public array $formatters = [
        'product_image' => 'image',
    ];

    public function itemToArray($item): array
    {
        $returnArray = parent::itemToArray($item);
        $returnArray['product_image'] = $item->getAvatarUrl();

        return $returnArray;
    }
}
