<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SerialNumberRange;
use TeamNiftyGmbH\DataTable\DataTable;

class SerialNumberRangeList extends DataTable
{
    protected string $model = SerialNumberRange::class;

    public array $enabledCols = [
        'client.name',
        'type',
        'current_number',
        'prefix',
        'suffix',
    ];
}
