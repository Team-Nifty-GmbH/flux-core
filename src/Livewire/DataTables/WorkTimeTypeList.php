<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\WorkTimeType;
use TeamNiftyGmbH\DataTable\DataTable;

class WorkTimeTypeList extends DataTable
{
    protected string $model = WorkTimeType::class;

    public array $enabledCols = [
        'name',
        'is_billable',
    ];
}
