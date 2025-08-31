<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AbsenceType;

class AbsenceTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'code',
        'color',
        'employee_can_create',
        'affects_vacation',
        'affects_overtime',
        'affects_sick',
        'is_active',
    ];

    protected string $model = AbsenceType::class;
}
