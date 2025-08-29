<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\EmployeeDay;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceRequestEmployeeDay extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $table = 'absence_request_employee_day';

    public function absenceRequest(): BelongsTo
    {
        return $this->belongsTo(AbsenceRequest::class);
    }

    public function employeeDay(): BelongsTo
    {
        return $this->belongsTo(EmployeeDay::class);
    }
}
