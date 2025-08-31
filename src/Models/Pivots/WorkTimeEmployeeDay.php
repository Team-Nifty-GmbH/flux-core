<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\EmployeeDay;
use FluxErp\Models\WorkTime;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTimeEmployeeDay extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'pivot_id';

    protected $table = 'work_time_employee_day';

    public function employeeDay(): BelongsTo
    {
        return $this->belongsTo(EmployeeDay::class);
    }

    public function workTime(): BelongsTo
    {
        return $this->belongsTo(WorkTime::class);
    }
}
