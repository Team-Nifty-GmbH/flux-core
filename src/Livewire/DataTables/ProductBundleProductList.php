<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Pivots\ProductBundleProduct;

class ProductBundleProductList extends BaseDataTable
{
    public array $enabledCols = [
        'count',
        'bundle_product.name',
    ];

    protected string $model = ProductBundleProduct::class;
}
