<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Unit;

class UnitList extends BaseDataTable
{
    protected string $model = Unit::class;

    public array $enabledCols = [
        'name',
        'abbreviation',
    ];
}
