<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\Employee;

class EmployeeList extends BaseDataTable
{
    public array $enabledCols = [
        'id',
        'name',
        'job_title',
        'phone_mobile',
        'email',
        'health_insurance',
        'probation_period_until',
        'fixed_term_contract_until',
        'is_active',
    ];

    public bool $hasNoRedirect = true;

    protected string $model = Employee::class;
}
