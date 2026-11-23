<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\HumanResources\EmployeeDays as BaseEmployeeDays;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;

class EmployeeDays extends BaseEmployeeDays
{
    #[Locked, Modelable]
    public ?int $employeeId = null;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('employee_id', $this->employeeId);
    }
}
