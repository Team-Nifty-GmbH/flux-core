<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\HumanResources\AbsenceRequests as BaseAbsenceRequests;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;

class AbsenceRequests extends BaseAbsenceRequests
{
    #[Locked, Modelable]
    public ?int $employeeId = null;

    public function canChooseEmployee(): bool
    {
        return false;
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('employee_id', $this->employeeId);
    }
}
