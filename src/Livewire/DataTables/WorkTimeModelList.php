<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\WorkTimeModel;

class WorkTimeModelList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'cycle_weeks',
        'weekly_hours',
        'annual_vacation_days',
        'is_active',
    ];

    protected string $model = WorkTimeModel::class;
}
