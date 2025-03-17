<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Unit;

class UnitList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'abbreviation',
    ];

    protected string $model = Unit::class;
}
