<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationBlackoutEmployeeDepartment extends FluxPivot
{
    public $incrementing = true;

    public $primaryKey = 'pivot_id';

    public $timestamps = false;

    protected $table = 'vacation_blackout_employee_department';

    public function employeeDepartment(): BelongsTo
    {
        return $this->belongsTo(EmployeeDepartment::class);
    }

    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
