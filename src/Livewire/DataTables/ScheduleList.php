<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Schedule;

class ScheduleList extends BaseDataTable
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
