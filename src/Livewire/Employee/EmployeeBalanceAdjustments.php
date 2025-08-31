<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\DataTables\EmployeeBalanceAdjustmentList;
use FluxErp\Livewire\Forms\EmployeeBalanceAdjustmentForm;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTableHasFormEdit;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class EmployeeBalanceAdjustments extends EmployeeBalanceAdjustmentList
{
    use DataTableHasFormEdit {
        DataTableHasFormEdit::save as baseSave;
    }

    #[DataTableForm]
    public EmployeeBalanceAdjustmentForm $employeeBalanceAdjustmentForm;

    #[Modelable]
    public ?int $employeeId = null;

    public ?string $includeBefore = 'flux::livewire.employee.employee-balance-adjustments';

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('employee_id', $this->employeeId);
    }

    public function save(): bool
    {
        $this->employeeBalanceAdjustmentForm->employee_id = $this->employeeId;

        return $this->baseSave();
    }
}
