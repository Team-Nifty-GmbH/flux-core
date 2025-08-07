<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTimeModelSchedule extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'week_number' => 'integer',
        'weekday' => 'integer',
        'break_minutes' => 'decimal:2',
        'work_hours' => 'decimal:2',
    ];

    public function workTimeModel(): BelongsTo
    {
        return $this->belongsTo(WorkTimeModel::class);
    }
}