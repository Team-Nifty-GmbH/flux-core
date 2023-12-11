<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Warehouse;
use TeamNiftyGmbH\DataTable\DataTable;

class WarehouseList extends DataTable
{
    protected string $model = Warehouse::class;

    public array $enabledCols = [
        'name',
    ];
}
