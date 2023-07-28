<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkTime extends Model
{
    use HasPackageFactory, HasUuid;

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_pause' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('trackable');
    }

    public function workTimeType(): BelongsTo
    {
        return $this->belongsTo(WorkTimeType::class);
    }
}
