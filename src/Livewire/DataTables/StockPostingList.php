<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\StockPosting;

class StockPostingList extends BaseDataTable
{
    public array $columnLabels = [
        'warehouse_bin.code' => 'Warehouse Bin',
        'lot.lot_number' => 'Lot',
    ];

    public array $enabledCols = [
        'warehouse.name',
        'warehouse_bin.code',
        'lot.lot_number',
        'posting',
        'description',
        'stock',
        'created_at',
        'created_by',
    ];

    public array $formatters = [
        'posting' => 'coloredFloat',
        'stock' => 'coloredFloat',
    ];

    protected string $model = StockPosting::class;
}
