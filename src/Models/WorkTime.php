<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Contracts\Calendarable;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class WorkTime extends FluxModel implements Calendarable
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
                $workTime->broadcastEvent('dailyUpdated');
            } else {
                $workTime->broadcastEvent('taskUpdated');
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

    public static function toCalendar(): array
    {
        return resolve_static(User::class, 'query')
            ->where('is_active', true)
            ->get()
            ->map(fn (User $user) => [
                'id' => Str::of(static::class)->replace('\\', '.') . '.' . $user->id,
                'modelType' => morph_alias(static::class),
                'name' => $user->name,
                'color' => $user->color,
                'resourceEditable' => false,
                'hasRepeatableEvents' => false,
                'isPublic' => false,
                'isShared' => false,
                'permission' => 'owner',
                'group' => 'other',
                'isVirtual' => true,
            ])
            ->toArray();
    }

    public function toCalendarEvent(?array $info = null): array
    {
        return [
            'id' => $this->id,
            'calendar_type' => $this->getMorphClass(),
            'title' => $this->user->name,
            'start' => $this->started_at->toDateTimeString(),
            'end' => $this->ended_at?->toDateTimeString(),
            'color' => $this->user->color,
            'invited' => [],
            'description' => $this->description,
            'allDay' => false,
            'is_editable' => true,
            'is_invited' => false,
            'is_public' => false,
            'is_repeatable' => false,
        ];
    }

    public function scopeInTimeframe(
        Builder $builder,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?array $info = null
    ): void {
        if ($start) {
            $builder->where('started_at', '>=', $start);
        }

        if ($end) {
            $builder->where('ended_at', '<=', $end);
        }

        $builder->where('is_daily_work_time', true)
            ->where('user_id', Str::afterLast(data_get($info, 'id'), '.'))
            ->where('is_locked', true)
            ->where('is_pause', false);
    }

    public static function fromCalendarEvent(array $event): Model
    {
        // TODO: Implement fromCalendarEvent() method.
    }
}
