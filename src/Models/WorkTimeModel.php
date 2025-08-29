<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTimeModel extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'cycle_weeks' => 'integer',
            'work_days_per_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getDailyWorkHours(?Carbon $date = null): string
    {
        $scheduledHours = $date
            ? $this->schedules()
                ->where('weekday', $date->dayOfWeek)
                ->value('work_hours')
            : null;

        return $scheduledHours ?? bcdiv($this->weekly_hours, $this->work_days_per_week) ?? 0;
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkTimeModelSchedule::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
