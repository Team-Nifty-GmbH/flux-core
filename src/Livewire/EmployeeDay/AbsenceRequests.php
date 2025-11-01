<?php

namespace FluxErp\Livewire\EmployeeDay;

use FluxErp\Livewire\HumanResources\AbsenceRequests as BaseAbsenceRequests;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class AbsenceRequests extends BaseAbsenceRequests
{
    #[Modelable]
    public ?int $employeeDayId = null;

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereHas('employeeDays', function (Builder $query): void {
            $query->whereKey($this->employeeDayId);
        });
    }
}
