<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Printer;

class PrinterList extends BaseDataTable
{
    protected string $model = Printer::class;

    public array $enabledCols = [
        'name',
        'spooler_name',
        'location',
        'make_and_model',
        'is_active',
    ];
}
