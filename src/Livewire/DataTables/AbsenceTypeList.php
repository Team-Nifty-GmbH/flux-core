<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\AbsenceType;

class AbsenceTypeList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'code',
        'color',
        'employee_can_create_enum',
        'affects_vacation',
        'affects_sick_leave',
        'affects_overtime',
        'is_active',
    ];

    protected string $model = AbsenceType::class;
}
