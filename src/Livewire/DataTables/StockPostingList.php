<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\StockPosting;

class StockPostingList extends BaseDataTable
{
    public array $columnLabels = [
        'storage_area.code' => 'Storage Area',
        'lot.lot_number' => 'Lot',
    ];

    public array $enabledCols = [
        'warehouse.name',
        'storage_area.code',
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
