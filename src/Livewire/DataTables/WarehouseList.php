<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Task;
use FluxErp\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class WarehouseList extends DataTable
{
    protected string $model = Warehouse::class;

    public array $enabledCols = [
        'name',
    ];
}
