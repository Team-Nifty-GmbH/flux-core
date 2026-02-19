<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Pivots\BundleProductProduct;
use Livewire\Attributes\Locked;

class ProductBundleProductList extends BaseDataTable
{
    public array $enabledCols = [
        'count',
        'bundle_product.name',
    ];

    #[Locked]
    public ?string $modelKeyName = 'pivot_id';

    protected string $model = BundleProductProduct::class;
}
