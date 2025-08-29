<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Contracts\Calendarable;
use FluxErp\Contracts\Targetable;
use FluxErp\Enums\AbsenceRequestStatusEnum;
use FluxErp\Models\Pivots\WorkTimeEmployeeDay;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class WorkTime extends FluxModel implements Calendarable, Targetable
{
    use Filterable, HasPackageFactory, HasParentChildRelations, HasUuid, SoftDeletes;

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
            // Set employee_id if user has an employee relation
            if ($workTime->user_id && ! $workTime->employee_id) {
                $user = resolve_static(User::class, 'query')->find($workTime->user_id);
                if ($user && $user->employee) {
                    $workTime->employee_id = $user->employee->getKey();
                }
            }

            // Calculate total_time_ms when started_at and ended_at are present
            if ($workTime->started_at && $workTime->ended_at) {
                $workTime->total_time_ms = bcsub(
                    $workTime->started_at->diffInMilliseconds($workTime->ended_at),
                    $workTime->paused_time_ms ?? 0,
                    0
                );

                // For pause entries, make the time negative
                if ($workTime->is_pause) {
                    $workTime->total_time_ms = bcmul($workTime->total_time_ms, -1, 0);
                }
            }

            if ($workTime->is_locked) {
                $workTime->calculateTotalCost();
            }
        });

        static::saved(function (WorkTime $workTime): void {
            if ($workTime->is_daily_work_time) {
                $workTime->broadcastEvent('dailyUpdated', toEveryone: true);

                // Update overtime hours when a locked daily work time is saved
                if ($workTime->is_locked && ! $workTime->is_pause && $workTime->user_id) {
                    $workTime->updateUserOvertimeHours();
                }
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeDays(): BelongsToMany
    {
        return $this->belongsToMany(EmployeeDay::class, 'work_time_employee_day')
            ->using(WorkTimeEmployeeDay::class)
            ->withPivot(['hours_contributed', 'break_minutes_contributed'])
            ->withTimestamps();
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

    protected function calculateUserTargetHours(User $user): string
    {
        // Get the employment date
        $employmentDate = $user->employment_date ? Carbon::parse($user->employment_date) : null;
        $startDate = $employmentDate ?? Carbon::now()->startOfYear();
        $endDate = Carbon::now();

        // Get all work time models for this user in the period
        $workTimeModels = $user->employee
            ?->workTimeModelHistory()
            ->with('workTimeModel.schedules')
            ->get();

        $totalTargetHours = 0;

        // Get all approved absences that count as target hours
        $absencesCountingAsTargetHours = resolve_static(AbsenceRequest::class, 'query')
            ->where('employee_id', $user->employee->getKey())
            ->where('status', AbsenceRequestStatusEnum::Approved)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->with('absenceType')
            ->get()
            ->filter(fn ($absence) => $absence->absenceType?->counts_as_target_hours ?? false);

        // Calculate target hours for each day
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            // Skip future dates
            if ($currentDate->isFuture()) {
                $currentDate->addDay();

                continue;
            }

            // Find which work time model was active on this date
            $activeModel = null;
            foreach ($workTimeModels ?? [] as $history) {
                $validFrom = Carbon::parse($history->valid_from);
                $validUntil = $history->valid_until ? Carbon::parse($history->valid_until) : null;

                if ($currentDate->gte($validFrom) && (! $validUntil || $currentDate->lte($validUntil))) {
                    $activeModel = $history->workTimeModel;
                    break;
                }
            }

            if ($activeModel && $activeModel->schedules) {
                $dayOfWeek = $currentDate->dayOfWeek;
                $dbWeekday = $dayOfWeek === 0 ? 7 : $dayOfWeek;

                // Calculate which week of the schedule cycle we're in
                $maxWeek = $activeModel->schedules->max('week_number') ?: 1;
                $weekOfYear = $currentDate->isoWeek;
                $cycleWeek = (($weekOfYear - 1) % $maxWeek) + 1;

                // Get the schedule for this specific week and day
                $daySchedule = $activeModel->schedules
                    ->where('weekday', $dbWeekday)
                    ->where('week_number', $cycleWeek)
                    ->first();

                if ($daySchedule && $daySchedule->work_hours > 0) {
                    // Calculate NET work hours (gross hours minus break)
                    $grossHours = abs($daySchedule->work_hours);
                    $breakHours = ($daySchedule->break_minutes ?? 0) / 60;
                    $netHours = $grossHours - $breakHours;

                    // Check if there's an absence that counts as target hours on this day
                    $hasAbsenceCountingAsTarget = $absencesCountingAsTargetHours->contains(function ($absence) use ($currentDate) {
                        $start = Carbon::parse($absence->start_date);
                        $end = Carbon::parse($absence->end_date);

                        return $currentDate->between($start, $end);
                    });

                    // Use Employee's isWorkDay method to check holidays and work schedule
                    $isWorkDay = $user->employee ? $user->employee->isWorkDay($currentDate) : true;

                    // Don't count non-work days, but do count absences that count as target hours
                    if ($isWorkDay || $hasAbsenceCountingAsTarget) {
                        $totalTargetHours = bcadd($totalTargetHours, $netHours, 2);
                    }
                }
            }

            $currentDate->addDay();
        }

        return (string) $totalTargetHours;
    }

    protected function updateUserOvertimeHours(): void
    {
        if (! $this->user_id) {
            return;
        }

        $user = $this->user;
        if (! $user) {
            return;
        }

        // Calculate total worked hours for the user
        $totalWorkedMs = resolve_static(WorkTime::class, 'query')
            ->where('user_id', $this->user_id)
            ->where('is_daily_work_time', true)
            ->where('is_locked', true)
            ->where('is_pause', false)
            ->sum('total_time_ms');

        // Convert to hours
        $totalWorkedHours = bcdiv($totalWorkedMs, 3600000, 2); // ms to hours

        // Get the user's target hours from their work time model
        $targetHours = $this->calculateUserTargetHours($user);

        // Calculate overtime
        $overtimeHours = bcsub($totalWorkedHours, $targetHours, 2);

        // Update user's overtime_hours using the UpdateUser action
        UpdateUser::make([
            'id' => $user->getKey(),
            'overtime_hours' => $overtimeHours,
        ])
            ->validate()
            ->execute();
    }
}
