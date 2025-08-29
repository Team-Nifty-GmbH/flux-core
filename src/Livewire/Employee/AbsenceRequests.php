<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\HumanResources\AbsenceRequests as BaseAbsenceRequests;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class AbsenceRequests extends BaseAbsenceRequests
{
    #[Modelable]
    public ?int $employeeId = null;

    public function canChooseEmployee(): bool
    {
        return false;
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->where('employee_id', $this->employeeId);
    }
}
