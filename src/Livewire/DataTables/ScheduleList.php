<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Schedule;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class ScheduleList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = Schedule::class;

    public array $enabledCols = [
        'name',
        'description',
        'type',
        'due_at',
        'is_active',
    ];
}
