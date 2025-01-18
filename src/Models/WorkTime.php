<?php

namespace FluxErp\Models;

use FluxErp\Support\Calculation\Rounding;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkTime extends FluxModel
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

        static::saving(function (WorkTime $workTime) {
            if ($workTime->is_locked) {
                $workTime->calculateTotalCost();
            }
        });

        static::saved(function (WorkTime $workTime) {
            if ($workTime->is_daily_work_time) {
                $workTime->broadcastEvent('dailyUpdated', toEveryone: true);
            } else {
                $workTime->broadcastEvent('taskUpdated', toEveryone: true);
            }
        });
    }

    public static function getGenericChannelEvents(): array
    {
        return array_merge(
            parent::getGenericChannelEvents(),
            [
                'dailyUpdated',
                'taskUpdated',
            ]
        );
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(WorkTime::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workTimeType(): BelongsTo
    {
        return $this->belongsTo(WorkTimeType::class);
    }

    public function calculateTotalCost(): static
    {
        $this->total_cost = Rounding::round(
            bcmul(
                $this->user->cost_per_hour,
                bcdiv($this->total_time_ms, 3600000),
                9
            )
        );

        if ($this->model && method_exists($this->model, 'costColumn')
            && $costColumn = $this->model->costColumn()
        ) {
            $this->model->{$costColumn} = bcadd(
                $this->model->{$costColumn},
                $this->total_cost,
                2
            );
            $this->model->save();
        }

        return $this;
    }
}
