<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\PrintJob;

class PrintJobList extends BaseDataTable
{
    protected string $model = PrintJob::class;

    public array $enabledCols = [
        'user.name',
        'media.name',
        'printer.name',
        'quantity',
        'size',
        'is_completed',
    ];
}
