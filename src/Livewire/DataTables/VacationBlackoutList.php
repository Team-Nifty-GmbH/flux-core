<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\VacationBlackout;

class VacationBlackoutList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected string $model = VacationBlackout::class;
}
