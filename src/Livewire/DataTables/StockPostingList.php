<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\StockPosting;
use TeamNiftyGmbH\DataTable\DataTable;

class StockPostingList extends DataTable
{
    protected string $model = StockPosting::class;

    public array $enabledCols = [
        'warehouse.name',
        'posting',
        'stock',
        'created_at',
        'created_by.name',
    ];

    public array $formatters = [
        'posting' => 'coloredFloat',
        'stock' => 'coloredFloat',
    ];
}
