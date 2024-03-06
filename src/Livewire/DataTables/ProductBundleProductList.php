<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Pivots\ProductBundleProduct;

class ProductBundleProductList extends BaseDataTable
{
    protected string $model = ProductBundleProduct::class;

    public array $enabledCols = [
        'count',
        'bundle_product.name',
    ];
}
