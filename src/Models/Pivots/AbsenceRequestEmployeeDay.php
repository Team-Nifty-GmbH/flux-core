<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\EmployeeDay;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceRequestEmployeeDay extends FluxPivot
{
    protected $table = 'absence_request_employee_day';

    // Relations
    /**
     * @return BelongsTo<AbsenceRequest, $this>
     */
    public function absenceRequest(): BelongsTo
    {
        return $this->belongsTo(AbsenceRequest::class);
    }

    /**
     * @return BelongsTo<EmployeeDay, $this>
     */
    public function employeeDay(): BelongsTo
    {
        return $this->belongsTo(EmployeeDay::class);
    }
}
