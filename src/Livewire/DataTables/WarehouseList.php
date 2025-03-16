<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Warehouse;

class WarehouseList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
    ];

    protected string $model = Warehouse::class;
}
