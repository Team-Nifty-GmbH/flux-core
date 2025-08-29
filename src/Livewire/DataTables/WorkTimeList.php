<?php

namespace FluxErp\Livewire\DataTables;

class WorkTimeList extends BaseDataTable
{
    public array $enabledCols = [
        'user.name',
        'name',
        'total_time_ms',
        'paused_time_ms',
        'started_at',
        'ended_at',
        'is_billable',
        'is_locked',
        'is_daily_work_time',
    ];

    public array $formatters = [
        'total_time_ms' => 'time',
        'paused_time_ms' => 'time',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
