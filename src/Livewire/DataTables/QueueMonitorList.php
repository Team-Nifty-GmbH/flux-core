<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\QueueMonitor;

class QueueMonitorList extends BaseDataTable
{
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
