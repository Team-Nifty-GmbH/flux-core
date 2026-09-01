<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\EmployeeDay;
use FluxErp\Models\WorkTime;
use FluxErp\Traits\Model\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDayWorkTime extends FluxPivot
{
    use HasPackageFactory;

    protected $table = 'employee_day_work_time';

    // Relations
    /**
     * @return BelongsTo<EmployeeDay, $this>
     */
    public function employeeDay(): BelongsTo
    {
        return $this->belongsTo(EmployeeDay::class);
    }

    /**
     * @return BelongsTo<WorkTime, $this>
     */
    public function workTime(): BelongsTo
    {
        return $this->belongsTo(WorkTime::class);
    }
}
