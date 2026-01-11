<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PrintJob;

class PrintJobList extends BaseDataTable
{
    public array $enabledCols = [
        'user.name',
        'media.name',
        'media.disk',
        'printer.name',
        'quantity',
        'size',
        'is_completed',
    ];

    protected string $model = PrintJob::class;
}
