<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Holiday;

class HolidayList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'month',
        'day',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected string $model = Holiday::class;
}
