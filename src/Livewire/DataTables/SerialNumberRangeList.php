<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\SerialNumberRange;

class SerialNumberRangeList extends BaseDataTable
{
    public array $enabledCols = [
        'tenant.name',
        'type',
        'current_number',
        'prefix',
        'suffix',
    ];

    protected string $model = SerialNumberRange::class;
}
