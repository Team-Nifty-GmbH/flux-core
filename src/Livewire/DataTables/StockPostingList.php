<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\StockPosting;

class StockPostingList extends BaseDataTable
{
    protected string $model = StockPosting::class;

    public array $enabledCols = [
        'warehouse.name',
        'posting',
        'stock',
        'created_at',
        'created_by',
    ];

    public array $formatters = [
        'posting' => 'coloredFloat',
        'stock' => 'coloredFloat',
    ];
}
