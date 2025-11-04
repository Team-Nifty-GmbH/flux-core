<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\DayPartEnum;
use FluxErp\Models\Pivots\AbsenceRequestEmployeeDay;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class AbsenceRequest extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use Commentable, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity, SoftDeletes, Trackable;

    public ?string $stateChangeComment = null;

    protected static function booted(): void
    {
        static::saving(function (AbsenceRequest $absenceRequest): void {
            if ($absenceRequest->day_part?->value !== AbsenceRequestDayPartEnum::Time) {
                $absenceRequest->start_time = null;
                $absenceRequest->end_time = null;
            }

            if ($absenceRequest
                ->isDirty([
                    'employee_id',
                    'day_part',
                    'start_date',
                    'end_date',
                    'start_time',
                    'end_time',
                ])
            ) {
                $absenceRequest->days_requested = $absenceRequest->start_date
                    ->diffInDays($absenceRequest->end_date, absolute: true) + 1;
                $absenceRequest->work_hours_affected = $absenceRequest->calculateWorkHoursAffected();
                $absenceRequest->work_days_affected = $absenceRequest->calculateWorkDaysAffected();
            }
        });

        static::saved(function (AbsenceRequest $absenceRequest): void {
            if (! $absenceRequest->wasChanged('state')) {
                return;
            }

            activity()
                ->performedOn($absenceRequest)
                ->causedBy(auth()->user())
                ->event($absenceRequest->state->value)
                ->useLog('absence_request_state_changes')
                ->log($absenceRequest->stateChangeComment ?? '');

            $absenceRequest->stateChangeComment = null;

            $employeeDays = $absenceRequest->employeeDays()->pluck('date', 'id');
            foreach ($employeeDays as $date) {
                CloseEmployeeDay::make([
                    'employee_id' => $absenceRequest->employee_id,
                    'date' => $date,
                ])
                    ->validate()
                    ->execute();
            }

            if ($absenceRequest->state === AbsenceRequestStateEnum::Approved) {
                $current = $absenceRequest->start_date->copy();
                while ($current->lte($absenceRequest->end_date)) {
                    if ($employeeDays->doesntContain($current)
                        && $absenceRequest->employee->isWorkDay($current)
                    ) {
                        CloseEmployeeDay::make([
                            'employee_id' => $absenceRequest->employee_id,
                            'date' => $current,
                        ])
                            ->validate()
                            ->execute();
                    }

                    $current->addDay();
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'state' => AbsenceRequestStateEnum::class,
            'day_part' => AbsenceRequestDayPartEnum::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'sick_note_issued_date' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'is_emergency' => 'boolean',
        ];
    }

    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function calculateWorkDaysAffected(?Carbon $date = null): string|float
    {
        $deductionRate = $this->absenceType->percentage_deduction ?? 1;
        $dayPartFraction = match ($this->day_part?->value) {
            DayPartEnum::FullDay => 1,
            DayPartEnum::FirstHalf, DayPartEnum::SecondHalf, => 0.5,
            default => 0,
        };

        $totalDays = 0;
        $current = ($date ?? $this->start_date)->copy();
        while ($current->lte($date ?? $this->end_date)) {
            if ($this->employee->isWorkDay($current)) {
                $dayPartFraction = $this->getDayPartFractionByTime($current) ?? $dayPartFraction;

                // Apply the deduction rate to get the actual days affected
                $totalDays = bcadd(
                    $totalDays,
                    bcround(bcmul($dayPartFraction, $deductionRate), 2)
                );
            }

            $current->addDay();
        }

        return $totalDays;
    }

    public function calculateWorkHoursAffected(?Carbon $date = null): string|int|float
    {
        $deductionRate = $this->absenceType->percentage_deduction ?? 1;
        $dayPartFraction = match ($this->day_part?->value) {
            DayPartEnum::FullDay => 1,
            DayPartEnum::FirstHalf, DayPartEnum::SecondHalf, => 0.5,
            default => 0,
        };

        $totalHours = 0;
        $current = ($date ?? $this->start_date)->copy();

        while ($current->lte($date ?? $this->end_date)) {
            if ($this->employee->isWorkDay($current)) {
                $dailyHours = $this->employee->getWorkTimeModel($current)->getDailyWorkHours($current);
                $dayPartFraction = $this->getDayPartFractionByTime($current) ?? $dayPartFraction;

                $totalHours = bcadd(
                    $totalHours,
                    bcmul(
                        bcmul($dailyHours, $dayPartFraction),
                        $deductionRate
                    )
                );
            }

            $current->addDay();
        }

        return $totalHours;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeDays(): BelongsToMany
    {
        return $this->belongsToMany(EmployeeDay::class)
            ->using(AbsenceRequestEmployeeDay::class);
    }

    public function failsAbsencePolicies(): ?array
    {
        return $this->absenceType
            ->absencePolicies
            ->map(fn (AbsencePolicy $policy) => $policy->validateRequest($this))
            ->filter()
            ->toArray();
    }

    public function getAvatarUrl(): ?string
    {
        return route('avatar', [
            'text' => $this->absenceType->code,
            'color' => Str::after($this->absenceType->color, '#'),
        ]);
    }

    public function getDescription(): ?string
    {
        return Str::limit($this->reason, 50);
    }

    public function getLabel(): ?string
    {
        return $this->absenceType->name
            . ' - '
            . $this->employee->name
            . ' (' . $this->start_date->format('Y-m-d')
            . ' ' . __('to') . ' ' . $this->end_date->format('Y-m-d')
            . ')';
    }

    public function getUrl(): ?string
    {
        return route('human-resources.absence-requests.show', $this->id);
    }

    public function intersections(array $excludeStates = []): Builder
    {
        return static::query()
            ->whereKeyNot($this->getKey())
            ->where('employee_id', $this->employee_id)
            ->when($excludeStates, fn (Builder $query) => $query->whereNotIn('state', $excludeStates))
            ->where(function (Builder $query): void {
                $query
                    ->where('start_date', '<=', $this->end_date)
                    ->where('end_date', '>=', $this->start_date)
                    ->where(fn (Builder $query) => $query
                        ->where('day_part', DayPartEnum::FullDay)
                        ->orWhere('day_part', $this->day_part?->value)
                        ->when(
                            $this->day_part?->value === AbsenceRequestDayPartEnum::Time,
                            fn (Builder $query) => $query
                                ->where('start_time', '<=', $this->end_time)
                                ->where('end_time', '>=', $this->start_time)
                        )
                    );
            });
    }

    public function isInBlackoutPeriod(): bool
    {
        return resolve_static(VacationBlackout::class, 'query')
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->where('start_date', '<=', $this->end_date)
                    ->where('end_date', '>=', $this->start_date);
            })
            ->where(function (Builder $query): void {
                $query->whereHas(
                    'employees',
                    fn (Builder $query) => $query->whereKey($this->employee_id)
                )
                    ->orWhereHas(
                        'locations',
                        fn (Builder $query) => $query->whereKey($this->employee->location_id)
                    )
                    ->orWhereHas(
                        'employeeDepartments',
                        fn (Builder $query) => $query->whereKey($this->employee->employee_department_id)
                    );
            })
            ->exists();
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    public function substitutes(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'absence_request_substitute');
    }

    protected function getDayPartFractionByTime(Carbon $date): ?string
    {
        if ($this->day_part?->value === AbsenceRequestDayPartEnum::Time) {
            $workTimeModel = $this->employee->getWorkTimeModel($date);

            $workDay = $workTimeModel->schedules()
                ->where('weekday', $date->dayOfWeek)
                ->first([
                    'id',
                    'start_time',
                    'end_time',
                    'break_minutes',
                    'work_hours',
                ]);

            $startTime = max($this->start_time, $workDay->start_time ?? '00:00:00');
            $endTime = min($this->end_time, $workDay->end_time ?? '23:59:59');
            $workHours = $workDay->work_hours ?? $workTimeModel->getDailyWorkHours($date);

            if (bccomp($workHours, 0) === 1) {
                $absenceHours = bcround(Carbon::parse($startTime)->diffInHours(Carbon::parse($endTime)), 2);

                return bcdiv($absenceHours, $workHours);
            }
        }

        return null;
    }
}
