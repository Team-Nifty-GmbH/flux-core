<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\HumanResources\EmployeeDays as BaseEmployeeDays;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class EmployeeDays extends BaseEmployeeDays
{
    #[Modelable]
    public ?int $employeeId = null;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('employee_id', $this->employeeId);
    }
}
