<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SerialNumberRange;

class SerialNumberRangeList extends BaseDataTable
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
