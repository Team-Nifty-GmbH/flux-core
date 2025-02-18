<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Industry;

class IndustryList extends BaseDataTable
{
    protected string $model = Industry::class;

    public array $enabledCols = [
        'name',
    ];
}
