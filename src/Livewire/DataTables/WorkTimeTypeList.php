<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\WorkTimeType;

class WorkTimeTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'is_billable',
    ];

    protected string $model = WorkTimeType::class;
}
