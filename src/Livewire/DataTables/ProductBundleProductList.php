<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Pivots\ProductBundleProduct;
use TeamNiftyGmbH\DataTable\DataTable;

class ProductBundleProductList extends DataTable
{
    protected string $model = ProductBundleProduct::class;

    public array $enabledCols = [
        'count',
        'bundle_product.name',
    ];
}
