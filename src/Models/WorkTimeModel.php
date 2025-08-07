<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTimeModel extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'cycle_weeks' => 'integer',
        'weekly_hours' => 'decimal:2',
        'annual_vacation_days' => 'integer',
        'max_overtime_hours' => 'decimal:2',
        'has_core_hours' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(WorkTimeModelSchedule::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}