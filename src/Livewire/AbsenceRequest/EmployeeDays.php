<?php

namespace FluxErp\Livewire\AbsenceRequest;

use FluxErp\Livewire\HumanResources\EmployeeDays as BaseEmployeeDays;
use FluxErp\Models\AbsenceRequest;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;

class EmployeeDays extends BaseEmployeeDays
{
    #[Modelable]
    public ?int $absenceRequestId = null;

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereAttachedTo(
            resolve_static(AbsenceRequest::class, 'query')
                ->whereKey($this->absenceRequestId)
                ->first('id'),
            'absenceRequests'
        );
    }
}
