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

    public static function toCalendar(): array
    {
        $bluePrint = [
            'resourceEditable' => false,
            'hasRepeatableEvents' => false,
            'isPublic' => false,
            'isShared' => false,
            'permission' => 'owner',
            'group' => 'other',
            'isVirtual' => true,
            'color' => '#0891b2',
        ];

        return [
            array_merge(
                $bluePrint,
                [
                    'id' => base64_encode(morph_alias(static::class)),
                    'modelType' => morph_alias(static::class),
                    'name' => __('Work Times'),
                    'hasNoEvents' => true,
                    'children' => resolve_static(User::class, 'query')
                        ->where('is_active', true)
                        ->get()
                        ->map(function (User $user) use (&$calendars, $bluePrint) {
                            $key = $user->getMorphClass() . ':' . $user->getKey();

                            return array_merge(
                                $bluePrint,
                                [
                                    'id' => base64_encode($key),
                                    'parentId' => base64_encode(morph_alias(static::class)),
                                    'modelType' => morph_alias(static::class),
                                    'name' => $user->name,
                                    'color' => $user->color ?? '#0891b2',
                                    'hasNoEvents' => true,
                                    'children' => [
                                        array_merge(
                                            $bluePrint,
                                            [
                                                'id' => base64_encode($key . ':work_time'),
                                                'parentId' => $key,
                                                'modelType' => morph_alias(static::class),
                                                'name' => $user->name . ' (' . __('Work Time') . ')',
                                                'color' => $user->color ?? '#0891b2',
                                            ]
                                        ),
                                    ],
                                ]
                            );
                        })
                        ->toArray(),
                ]
            ),
        ];
    }

    public static function fromCalendarEvent(array $event): Model
    {
        return resolve_static(static::class, 'query')
            ->whereKey(data_get($event, 'id'))
            ->first();
    }

    public function toCalendarEvent(?array $info = null): array
    {
        return [
            'id' => $this->id,
            'calendar_type' => $this->getMorphClass(),
            'title' => $this->user->name,
            'start' => $this->started_at->toDateTimeString(),
            'end' => $this->ended_at?->toDateTimeString(),
            'color' => $this->user->color ?? '#0891b2',
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
        $id = base64_decode(data_get($info, 'id') ?? '');
        if (! str_contains($id, ':')) {
            return;
        }

        $exploded = explode(':', $id);
        if (count($exploded) !== 3) {
            return;
        }

        $type = array_pop($exploded);
        $userId = array_pop($exploded);

        if ($start) {
            $builder->where('started_at', '>=', $start);
        }

        if ($end) {
            $builder->where('ended_at', '<=', $end);
        }

        $builder->where('user_id', $userId);

        if ($type && $this->hasNamedScope(Str::studly($type))) {
            $this->callNamedScope(Str::studly($type), ['builder' => $builder]);
        }
    }

    public function scopeWorkTime(Builder $builder): void
    {
        $builder->where('is_daily_work_time', true)
            ->where('is_locked', true)
            ->where('is_pause', false);
    }
}
