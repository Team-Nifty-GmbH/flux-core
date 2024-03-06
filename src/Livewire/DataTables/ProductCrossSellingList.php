<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\ProductCrossSelling;

class ProductCrossSellingList extends BaseDataTable
{
    protected string $model = ProductCrossSelling::class;

    public array $enabledCols = [
        'name',
        'is_active',
    ];
}
