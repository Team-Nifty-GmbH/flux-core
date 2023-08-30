<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\ProductCrossSelling;
use TeamNiftyGmbH\DataTable\DataTable;

class ProductCrossSellingList extends DataTable
{
    protected string $model = ProductCrossSelling::class;

    public array $enabledCols = [
        'name',
        'is_active',
    ];

    public function mount(): void
    {
        parent::mount();
    }
}
