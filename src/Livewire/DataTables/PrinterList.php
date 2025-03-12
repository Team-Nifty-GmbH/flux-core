<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Printer;

class PrinterList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'spooler_name',
        'location',
        'make_and_model',
        'is_active',
    ];

    protected string $model = Printer::class;
}
