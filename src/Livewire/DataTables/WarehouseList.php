<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Warehouse;

class WarehouseList extends BaseDataTable
{
    protected string $model = Warehouse::class;

    public array $enabledCols = [
        'name',
    ];
}
