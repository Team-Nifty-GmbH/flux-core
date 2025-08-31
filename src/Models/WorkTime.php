<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Contracts\Calendarable;
use FluxErp\Contracts\Targetable;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class WorkTime extends FluxModel implements Calendarable, Targetable
{
    use Filterable, HasPackageFactory, HasParentChildRelations, HasUserModification, HasUuid, SoftDeletes;

    public static function aggregateColumns(string $type): array
    {
        return match ($type) {
            'count' => ['id'],
            'avg', 'sum' => [
                'paused_time_ms',
                'total_time_ms',
                'total_cost',
            ],
            default => [],
        };
    }

    public static function aggregateTypes(): array
    {
        return [
            'avg',
            'count',
            'sum',
        ];
    }

    public static function fromCalendarEvent(array $event, string $action = 'update'): UpdateWorkTime
    {
        return UpdateWorkTime::make([
            'id' => data_get($event, 'id'),
            'name' => data_get($event, 'title'),
            'started_at' => data_get($event, 'start'),
            'ended_at' => data_get($event, 'end'),
            'description' => data_get($event, 'description'),
        ]);
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

    public static function ownerColumns(): array
    {
        return [
            'user_id',
            'created_by',
            'updated_by',
        ];
    }

    public static function timeframeColumns(): array
    {
        return [
            'started_at',
            'ended_at',
            'created_at',
            'updated_at',
        ];
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

    protected static function booted(): void
    {
        static::creating(function (WorkTime $workTime): void {
            $workTime->started_at = $workTime->started_at ?? now();
            $workTime->user_id = $workTime->user_id ?? auth()->id();
        });

        static::saving(function (WorkTime $workTime): void {
            if ($workTime->is_locked) {
                $workTime->calculateTotalCost();
            }
        });

        static::saved(function (WorkTime $workTime): void {
            if ($workTime->is_daily_work_time) {
                $workTime->broadcastEvent('dailyUpdated', toEveryone: true);
            } else {
                $workTime->broadcastEvent('taskUpdated', toEveryone: true);
            }
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
            'editable' => $this->is_locked ? false : true,
            'is_editable' => $this->is_locked ? false : true,
            'is_invited' => false,
            'is_public' => false,
            'is_repeatable' => false,
        ];
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
