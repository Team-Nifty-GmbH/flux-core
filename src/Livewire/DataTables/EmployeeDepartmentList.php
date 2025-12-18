<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Models\EmployeeDepartment;

class EmployeeDepartmentList extends BaseDataTable
{
    public array $enabledCols = [
        'name',
        'code',
        'location.name',
        'manager.name',
        'is_active',
    ];

    protected string $model = EmployeeDepartment::class;
}
