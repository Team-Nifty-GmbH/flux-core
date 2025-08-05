<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Industry;

class IndustryList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
    ];

    protected string $model = Industry::class;
}
