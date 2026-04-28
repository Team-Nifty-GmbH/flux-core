<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Employee;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeVacationBlackout extends FluxPivot
{
    protected $table = 'employee_vacation_blackout';

    // Relations
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
