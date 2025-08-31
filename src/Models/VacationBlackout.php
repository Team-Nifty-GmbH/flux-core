<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\VacationBlackoutEmployee;
use FluxErp\Models\Pivots\VacationBlackoutEmployeeDepartment;
use FluxErp\Models\Pivots\VacationBlackoutLocation;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VacationBlackout extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employeeDepartments(): BelongsToMany
    {
        return $this->belongsToMany(EmployeeDepartment::class, 'vacation_blackout_employee_department')
            ->using(VacationBlackoutEmployeeDepartment::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'vacation_blackout_employee')
            ->using(VacationBlackoutEmployee::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'vacation_blackout_location')
            ->using(VacationBlackoutLocation::class);
    }
}
