<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Livewire\DataTables\EmployeeList;
use FluxErp\Livewire\Forms\EmployeeForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;

class HrEmployeeSettings extends EmployeeList
{
    use DataTableHasFormEdit;

    #[DataTableForm]
    public EmployeeForm $employeeForm;

    public array $enabledCols = [
        'name',
        'email',
        'employee_number',
        'work_time_model.name',
        'location.name',
        'salary',
        'vacation_days_current',
        'employment_date',
    ];

    protected ?string $includeBefore = 'flux::livewire.settings.hr-employee-settings-modal';

    protected function getTableActions(): array
    {
        return [];
    }
}
