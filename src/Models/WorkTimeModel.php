<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTimeModel extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'overtime_compensation' => OvertimeCompensationEnum::class,
            'is_active' => 'boolean',
        ];
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_work_time_model')
            ->using(EmployeeWorkTimeModel::class);
    }

    public function getDailyWorkHours(?Carbon $date = null): string
    {
        $weekday = $date?->dayOfWeekIso;

        $scheduledHours = $weekday
            ? $this->schedules()
                ->where('weekday', $weekday)
                ->value('work_hours')
            : null;

        // If schedule exists, return it (even if 0)
        if ($scheduledHours !== null) {
            return $scheduledHours;
        }

        // No schedule - only calculate fallback for days within work_days_per_week
        if ($this->weekly_hours && $this->work_days_per_week) {
            // Only return calculated hours if weekday is within work days (1-5 for 5-day week)
            if ($weekday === null || $weekday <= $this->work_days_per_week) {
                return bcdiv($this->weekly_hours, $this->work_days_per_week);
            }
        }

        return 0;
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkTimeModelSchedule::class);
    }
}
