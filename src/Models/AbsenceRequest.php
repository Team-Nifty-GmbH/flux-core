<?php

namespace FluxErp\Models;

use Carbon\Carbon;
use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Enums\AbsenceRequestStatusEnum;
use FluxErp\Models\Pivots\AbsenceRequestEmployeeDay;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class AbsenceRequest extends FluxModel implements HasMedia, InteractsWithDataTables
{
    use Commentable, HasPackageFactory, HasStates, HasUserModification, HasUuid,
        InteractsWithMedia, LogsActivity, SoftDeletes, Trackable;

    public ?string $statusChangeComment = null;

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
            if (! $absenceRequest->wasChanged('status')) {
                return;
            }

            activity()
                ->performedOn($absenceRequest)
                ->causedBy(auth()->user())
                ->event($absenceRequest->status->value)
                ->useLog('absence_request_state_changes')
                ->log($absenceRequest->statusChangeComment ?? '');

            $absenceRequest->statusChangeComment = null;

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
            'start_date' => 'date',
            'end_date' => 'date',
            'sick_note_issued_date' => 'date',
            'approved_at' => 'datetime',
            'is_emergency' => 'boolean',
            'status' => AbsenceRequestStatusEnum::class,
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
            ->toArray();
    }

    public function getAvatarUrl(): ?string
    {
        return route('avatar', [
            'text' => Str::of($this->absenceType->name)
                ->replaceMatches('/[^A-Z]/', '')
                ->trim()
                ->limit(2, '')
                ->toString(),
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
            . ' to ' . $this->end_date->format('Y-m-d')
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
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function (Builder $query): void {
                        $query->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->exists();
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'substitute_employee_id');
    }

    public function substituteEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'substitute_employee_id');
    }

    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class, 'vacation_request_id');
    }
}
