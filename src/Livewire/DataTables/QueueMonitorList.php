<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\QueueMonitor;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class QueueMonitorList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = QueueMonitor::class;

    public array $enabledCols = [
        'job_batch.name',
        'name',
        'state',
        'progress',
        'created_at',
        'started_at',
        'finished_at',
    ];

    public array $formatters = [
        'progress' => 'progressPercentage',
    ];
}
