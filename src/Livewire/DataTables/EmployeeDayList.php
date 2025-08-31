<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\EmployeeDay;

class EmployeeDayList extends BaseDataTable
{
    public array $enabledCols = [
        'employee.name',
        'date',
        'target_hours',
        'actual_hours',
        'vacation_hours_used',
        'sick_hours_used',
        'plus_minus_overtime_hours',
        'plus_minus_absence_hours',
    ];

    public string $model = EmployeeDay::class;
}
