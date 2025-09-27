<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTimeModel extends FluxModel
{
    use HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'overtime_compensation_enum' => OvertimeCompensationEnum::class,
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
        $scheduledHours = $date
            ? $this->schedules()
                ->where('weekday', $date->dayOfWeek)
                ->value('work_hours')
            : null;

        return $scheduledHours
            ?? ($this->work_days_per_week ? bcdiv($this->weekly_hours, $this->work_days_per_week) : null)
            ?? 0;
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkTimeModelSchedule::class);
    }
}
