<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\EmployeeDepartmentVacationBlackout;
use FluxErp\Models\Pivots\EmployeeVacationBlackout;
use FluxErp\Models\Pivots\LocationVacationBlackout;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VacationBlackout extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

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
        return $this->belongsToMany(EmployeeDepartment::class, 'employee_department_vacation_blackout')
            ->using(EmployeeDepartmentVacationBlackout::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_vacation_blackout')
            ->using(EmployeeVacationBlackout::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'location_vacation_blackout')
            ->using(LocationVacationBlackout::class);
    }
}
