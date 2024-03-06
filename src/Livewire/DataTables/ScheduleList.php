<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Schedule;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class ScheduleList extends BaseDataTable
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
