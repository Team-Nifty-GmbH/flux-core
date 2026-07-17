<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDepartmentVacationBlackout extends FluxPivot
{
    protected $table = 'employee_department_vacation_blackout';

    // Relations
    public function employeeDepartment(): BelongsTo
    {
        return $this->belongsTo(EmployeeDepartment::class);
    }

    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
