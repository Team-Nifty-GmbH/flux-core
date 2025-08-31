<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Employee;
use FluxErp\Models\VacationBlackout;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationBlackoutEmployee extends FluxPivot
{
    public $incrementing = true;

    public $primaryKey = 'pivot_id';

    public $timestamps = false;

    protected $table = 'vacation_blackout_employee';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vacationBlackout(): BelongsTo
    {
        return $this->belongsTo(VacationBlackout::class);
    }
}
