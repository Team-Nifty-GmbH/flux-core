<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\FailedJob;

class FailedJobList extends BaseDataTable
{
    public array $enabledCols = [
        'connection',
        'queue',
        'failed_at',
        'exception',
    ];

    protected string $model = FailedJob::class;
}
