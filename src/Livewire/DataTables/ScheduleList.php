<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Schedule;
use TeamNiftyGmbH\DataTable\DataTable;

class ScheduleList extends DataTable
{
    protected string $model = Schedule::class;

    public array $enabledCols = [
        'name',
        'description',
        'type',
        'due_at',
        'is_active',
    ];
}
