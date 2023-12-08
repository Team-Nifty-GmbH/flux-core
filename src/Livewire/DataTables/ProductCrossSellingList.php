<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductCrossSelling;
use TeamNiftyGmbH\DataTable\DataTable;

class ProductCrossSellingList extends DataTable
{
    protected string $model = ProductCrossSelling::class;

    public array $enabledCols = [
        'name',
        'is_active',
    ];
}
