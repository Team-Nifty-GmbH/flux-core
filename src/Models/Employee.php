<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use DateTime;
use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Enums\SalutationEnum;
use FluxErp\Models\Pivots\EmployeeVacationBlackout;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Employee extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use Commentable, Communicatable, HasClientAssignment, HasFrontendAttributes, HasUserModification, HasUuid,
        InteractsWithMedia, LogsActivity, Notifiable, Searchable, SoftDeletes;

    public static string $iconName = 'user';

    protected string $detailRouteName = 'human-resources.employees.id';

    protected static function booted(): void
    {
        static::saving(function (Employee $employee): void {
            if ($employee->isDirty('lastname') || $employee->isDirty('firstname')) {
                $employee->name = trim($employee->firstname . ' ' . $employee->lastname);
            }

            if ($employee->isDirty('iban')) {
                $employee->iban = str_replace(' ', '', strtoupper($employee->iban));
            }
        });

        static::created(function (Employee $employee): void {
            if ($employee->user_id) {
                resolve_static(WorkTime::class, 'query')
                    ->withTrashed()
                    ->where('user_id', $employee->user_id)
                    ->update(['employee_id' => $employee->getKey()]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'salutation' => SalutationEnum::class,
            'date_of_birth' => 'date:Y-m-d',
            'employment_date' => 'date:Y-m-d',
            'termination_date' => 'date:Y-m-d',
            'probation_period_until' => 'date:Y-m-d',
            'fixed_term_contract_until' => 'date:Y-m-d',
            'work_permit_until' => 'date:Y-m-d',
            'residence_permit_until' => 'date:Y-m-d',
            'is_active' => 'boolean',
        ];
    }

    public function absenceRequests(): HasMany
    {
        return $this->hasMany(AbsenceRequest::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function employeeBalanceAdjustments(): HasMany
    {
        return $this->hasMany(EmployeeBalanceAdjustment::class);
    }

    public function employeeDays(): HasMany
    {
        return $this->hasMany(EmployeeDay::class);
    }

    public function employeeDepartment(): BelongsTo
    {
        return $this->belongsTo(EmployeeDepartment::class);
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar', 'thumb')
            ?: $this->user?->getAvatarUrl()
                ?: static::icon()->getUrl();
    }

    public function getCurrentOvertimeBalance(bool $includeAdjustments = true): string
    {
        return bcadd(
            $this->employeeDays()->sum('plus_minus_overtime_hours'),
            $includeAdjustments
                ? $this->employeeBalanceAdjustments()
                    ->where('type', EmployeeBalanceAdjustmentTypeEnum::Overtime)
                    ->where('effective_date', '<=', now())
                    ->sum('amount')
                : 0
        );
    }

    public function getCurrentVacationDaysBalance(): string
    {
        return $this->getVacationDaysBalance(now());
    }

    public function getDescription(): ?string
    {
        $parts = array_filter([
            $this->job_title,
            $this->email,
            $this->phone,
        ]);

        return implode(', ', $parts) ?: null;
    }

    public function getLabel(): ?string
    {
        return $this->name ?: $this->user?->name;
    }

    public function getTotalVacationDays(
        ?Carbon $start = null,
        ?Carbon $end = null,
        bool $includeAdjustments = true,
        ?Carbon $maxEffectiveDate = null,
    ): string {
        $totalDays = 0;
        $employmentDate = $this->employment_date ?? now();
        $periodStart = max($start, $employmentDate);
        $periodEnd = $end ?? now()->endOfYear();

        if ($this->termination_date) {
            $periodEnd = min($this->termination_date, $periodEnd);
        }

        $assignments = $this->workTimeModelHistory()
            ->with('workTimeModel')
            ->where(function (Builder $query) use ($periodStart, $periodEnd): void {
                $query->where('valid_from', '<=', $periodEnd)
                    ->where(function (Builder $query) use ($periodStart): void {
                        $query->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', $periodStart);
                    });
            })
            ->orderBy('valid_from')
            ->get();

        /** @var EmployeeWorkTimeModel $assignment */
        foreach ($assignments as $assignment) {
            $totalDays = bcadd(
                $totalDays,
                $assignment->getTotalVacationDays(start: $periodStart, end: $periodEnd)
            );
        }

        if ($includeAdjustments) {
            $maxEffectiveDate ??= $end;
            $adjustments = $this->employeeBalanceAdjustments()
                ->whereIn(
                    'type',
                    [
                        EmployeeBalanceAdjustmentTypeEnum::Vacation->value,
                        EmployeeBalanceAdjustmentTypeEnum::VacationCarryover->value,
                    ]
                )
                ->when($start, fn (Builder $query) => $query->whereDate('effective_date', '>=', $start))
                ->when(
                    $maxEffectiveDate,
                    fn (Builder $query) => $query->whereDate('effective_date', '<=', $maxEffectiveDate)
                )
                ->sum('amount');
        }

        $totalDays = bcadd($totalDays, $adjustments ?? 0, 2);

        return bcround($totalDays, 2);
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function getUsedVacationDays(?Carbon $start = null, ?Carbon $end = null): string
    {
        return $this->getVacationList($start, $end, false)
            ->reduce(fn (?string $carry, $item) => bcadd($carry, $item), 0);
    }

    public function getVacationDaysBalance(Carbon $date): string
    {
        return bcsub(
            $this->getTotalVacationDays(
                start: $start = $date->copy()->startOfYear(),
                end: $end = $date->copy()->endOfYear(),
                maxEffectiveDate: $date
            ),
            $this->getUsedVacationDays($start, $end)
        );
    }

    public function getVacationList(?Carbon $start = null, ?Carbon $end = null, bool $calculateHours = true): Collection
    {
        $column = $calculateHours ? 'vacation_hours_used' : 'vacation_days_used';

        $vacationDays = $this->employeeDays()
            ->when(
                $start,
                fn (Builder $query) => $query->whereDate('date', '>=', $start->toDateString())
            )
            ->when(
                $end,
                fn (Builder $query) => $query->whereDate('date', '<=', $end->toDateString())
            )
            ->where($column, '!=', 0)
            ->pluck($column, 'date');

        $this->absenceRequests()
            ->whereRelation('absenceType', 'affects_vacation', true)
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->when(
                $start,
                fn (Builder $query) => $query->whereDate('start_date', '>=', $start)
            )
            ->when(
                $end,
                fn (Builder $query) => $query->whereDate('start_date', '<=', $end)
            )
            ->whereDoesntHave('employeeDays')
            ->get([
                'id',
                'start_date',
                'end_date',
            ])
            ->each(function (AbsenceRequest $absenceRequest) use (&$vacationDays, $column, $end): void {
                $current = $absenceRequest->start_date;
                $periodEnd = min($absenceRequest->end_date, $end ?? $absenceRequest->end_date);

                while ($current->lte($periodEnd)) {
                    $vacationDuration = data_get(
                        resolve_static(
                            CloseEmployeeDay::class,
                            'calculateDayData',
                            [
                                'employee' => $this,
                                'date' => $current,
                            ]
                        ),
                        $column
                    ) ?? 0;

                    if (bccomp($vacationDuration, 0) !== 0
                        && bccomp(
                            $vacationDuration,
                            data_get($vacationDays, $current->toDateString()) ?? 0
                        ) === 1
                    ) {
                        $vacationDays[$current->toDateString()] = $vacationDuration;
                    }

                    $current = $current->addDay();
                }
            });

        return $vacationDays;
    }

    public function getWorkDaysInPeriod(Carbon $startDate, Carbon $endDate): array
    {
        $workDays = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if ($this->isWorkDay($current)) {
                $workDays[] = $current;
            }

            $current->addDay();
        }

        return $workDays;
    }

    public function getWorkHoursInPeriod(Carbon $startDate, Carbon $endDate): string|float|int
    {
        $workHours = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if ($this->isWorkDay($current)) {
                $workTimeModel = $this->getWorkTimeModel($current);
                $workHours = bcadd($workHours, $workTimeModel->getDailyWorkHours($current));
            }

            $current->addDay();
        }

        return $workHours;
    }

    public function getWorkTimeModel(?Carbon $date = null): ?WorkTimeModel
    {
        return $date
            ? $this->workTimeModelHistory()
                ->orderBy('valid_from', 'desc')
                ->where('valid_from', '<=', $date)
                ->with('workTimeModel')
                ->first()
                ?->workTimeModel
            : $this->workTimeModelHistory()
                ->whereNull('valid_until')
                ->with('workTimeModel')
                ->first()
                ?->workTimeModel;
    }

    public function isWorkDay(Carbon $date): bool
    {
        $workTimeModel = $this->workTimeModelHistory()
            ->where('valid_from', '<=', $date)
            ->first();

        if (! $workTimeModel) {
            return false;
        }

        if (
            resolve_static(Holiday::class, 'query')
                ->isHoliday($date, $this->location_id)
                ->exists()
        ) {
            return false;
        }

        $weekday = $date->dayOfWeekIso;
        $isWorkDay = $workTimeModel->workTimeModel->schedules()
            ->where('weekday', $weekday)
            ->exists();

        // If no schedule exists default to work_days_per_week
        if (! $isWorkDay) {
            $workDaysPerWeek = $workTimeModel->work_days_per_week ?? ceil($workTimeModel->weekly_hours / 10);
            for ($i = 1; $i <= $workDaysPerWeek; $i++) {
                if ($weekday === $i) {
                    return true;
                }
            }
        }

        return $isWorkDay;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('documents')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg',
                    'image/png',
                ]);
            })
            ->useDisk('private');
    }

    public function scopeEmployed(Builder $query, DateTime $untilDate): void
    {
        $query->whereHas('workTimeModelHistory')
            ->where('is_active', true)
            ->where(function (Builder $query) use ($untilDate): void {
                $query->where('employment_date', '<=', $untilDate)
                    ->whereNull('termination_date')
                    ->orWhereValueBetween($untilDate->startOfDay(), ['employment_date', 'termination_date']);
            });
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vacationBlackouts(): BelongsToMany
    {
        return $this->belongsToMany(VacationBlackout::class, 'employee_vacation_blackout')
            ->using(EmployeeVacationBlackout::class);
    }

    public function vacationCarryOverRule(): BelongsTo
    {
        return $this->belongsto(VacationCarryoverRule::class);
    }

    public function workTimeModelHistory(): HasMany
    {
        return $this->hasMany(EmployeeWorkTimeModel::class)
            ->orderBy('valid_from', 'desc');
    }

    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class);
    }
}
