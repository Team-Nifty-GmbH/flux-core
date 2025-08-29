<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Employee;

class EmployeeList extends BaseDataTable
{
    public array $enabledCols = [
        'id',
        'name',
        'job_title',
        'mobile_phone',
        'email',
        'health_insurance',
        'probation_period_until',
        'fixed_term_contract_until',
        'is_active',
    ];

    protected string $model = Employee::class;
}
