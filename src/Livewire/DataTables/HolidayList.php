<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Holiday;

class HolidayList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'date',
        'month',
        'day',
        'effective_from',
        'effective_until',
        'is_active',
        'is_half_day',
    ];

    protected string $model = Holiday::class;
}
