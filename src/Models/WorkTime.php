<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class WorkTime extends Model
{
    use Filterable, HasPackageFactory, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkTime $workTime) {
            $workTime->started_at = $workTime->started_at ?? now();
            $workTime->user_id = $workTime->user_id ?? auth()->id();
        });
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_billable' => 'boolean',
            'is_daily_work_time' => 'boolean',
            'is_locked' => 'boolean',
            'is_pause' => 'boolean',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo('trackable');
    }

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workTimeType(): BelongsTo
    {
        return $this->belongsTo(WorkTimeType::class);
    }
}
