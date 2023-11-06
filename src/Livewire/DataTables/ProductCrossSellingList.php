<?php

namespace FluxErp\Livewire\DataTables;

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

    public array $availableRelations = ['*'];

    public array $sortable = ['*'];

    public array $aggregatable = ['*'];

    public array $availableCols = ['*'];
}
