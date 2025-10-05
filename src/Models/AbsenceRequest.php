<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Enums\AbsenceRequestStateEnum;
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
            if ($absenceRequest->isDirty(['start_date', 'end_date', 'employee_id'])) {
                $absenceRequest->days_requested = $absenceRequest->start_date
                    ->diffInDays($absenceRequest->end_date, absolute: true) + 1;
                $absenceRequest->work_hours_affected = $absenceRequest->calculateWorkHoursAffected();
                $absenceRequest->work_days_affected = $absenceRequest->calculateWorkDaysAffected();
            }
        });

        static::saved(function (AbsenceRequest $absenceRequest): void {
            if (! $absenceRequest->wasChanged('state_enum')) {
                return;
            }

            activity()
                ->performedOn($absenceRequest)
                ->causedBy(auth()->user())
                ->event($absenceRequest->state_enum->value)
                ->useLog('absence_request_state_changes')
                ->log($absenceRequest->stateChangeComment ?? '');

            $absenceRequest->stateChangeComment = null;

            foreach ($absenceRequest->employeeDays()->get(['id', 'employee_id', 'date']) as $day) {
                CloseEmployeeDay::make([
                    'employee_id' => $absenceRequest->employee_id,
                    'date' => $day->date,
                ])
                    ->validate()
                    ->execute();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'state_enum' => AbsenceRequestStateEnum::class,
            'start_date' => 'datetime',
            'end_date' => 'datetime',
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
        $totalDays = 0;
        $current = ($date ?? $this->start_date)->copy();

        while ($current <= ($date ?? $this->end_date)) {
            if ($this->employee->isWorkDay($current)) {
                // Apply the deduction rate to get the actual days affected
                $totalDays = bcadd(
                    $totalDays,
                    bcmul(1, $deductionRate)
                );
            }

            $current->addDay();
        }

        return $totalDays;
    }

    public function calculateWorkHoursAffected(?Carbon $date = null): string|int|float
    {
        $deductionRate = $this->absenceType->percentage_deduction ?? 1;
        $totalHours = 0;
        $current = ($date ?? $this->start_date)->copy();

        while ($current <= ($date ?? $this->end_date)) {
            if ($this->employee->isWorkDay($current)) {
                $dailyHours = $this->employee->getWorkTimeModel($current)->getDailyWorkHours($current);
                $totalHours = bcadd(
                    $totalHours,
                    bcmul($dailyHours, $deductionRate)
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
}
