<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Pivots\BundleProductProduct;

class ProductBundleProductList extends BaseDataTable
{
    public array $enabledCols = [
        'count',
        'bundle_product.name',
    ];

    protected string $model = BundleProductProduct::class;
}
