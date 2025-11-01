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
        'vacation_days_used',
        'sick_days_used',
        'plus_minus_overtime_hours',
        'plus_minus_absence_hours',
    ];

    protected string $model = EmployeeDay::class;
}
