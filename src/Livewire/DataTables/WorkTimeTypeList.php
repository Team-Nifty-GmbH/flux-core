<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\WorkTimeType;

class WorkTimeTypeList extends BaseDataTable
{
    protected string $model = WorkTimeType::class;

    public array $enabledCols = [
        'name',
        'is_billable',
    ];
}
