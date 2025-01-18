<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\FailedJob;

class FailedJobList extends BaseDataTable
{
    protected string $model = FailedJob::class;

    public array $enabledCols = [
        'connection',
        'queue',
        'failed_at',
        'exception',
    ];
}
