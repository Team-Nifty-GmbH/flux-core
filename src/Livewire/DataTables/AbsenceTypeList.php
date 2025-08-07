<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AbsenceType;

class AbsenceTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'color',
        'employee_can_create',
        'counts_as_work_day',
        'is_active',
    ];

    protected string $model = AbsenceType::class;
}