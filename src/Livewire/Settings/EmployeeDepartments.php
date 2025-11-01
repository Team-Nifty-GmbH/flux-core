<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\EmployeeDepartmentList;
use FluxErp\Livewire\Forms\EmployeeDepartmentForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class EmployeeDepartments extends EmployeeDepartmentList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public EmployeeDepartmentForm $employeeDepartmentForm;

    protected ?string $includeBefore = 'flux::livewire.settings.employee-departments';
}
