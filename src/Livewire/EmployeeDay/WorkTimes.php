<?php

namespace FluxErp\Livewire\EmployeeDay;

use FluxErp\Livewire\HumanResources\WorkTimes as BaseWorkTimes;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class WorkTimes extends BaseWorkTimes
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
