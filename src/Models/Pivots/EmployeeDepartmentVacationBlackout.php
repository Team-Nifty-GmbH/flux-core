<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDepartmentVacationBlackout extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    public function employeeDepartment(): BelongsTo
    {
        return $this->belongsTo(EmployeeDepartment::class);
    }

    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
