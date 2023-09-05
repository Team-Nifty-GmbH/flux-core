<?php

namespace FluxErp\Http\Livewire\DataTables;

use FluxErp\Models\ProductCrossSelling;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class ProductCrossSellingList extends DataTable
{
    protected string $model = ProductCrossSelling::class;

    public array $enabledCols = [
        'name',
        'is_active',
    ];

    public function mount(): void
    {
        $this->availableCols = ModelInfo::forModel($this->model)
            ->attributes
            ->pluck('name')
            ->toArray();

        parent::mount();
    }
}
