<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductCrossSelling;

class ProductCrossSellingList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'is_active',
    ];

    protected string $model = ProductCrossSelling::class;
}
