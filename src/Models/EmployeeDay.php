<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\AbsenceRequestEmployeeDay;
use FluxErp\Models\Pivots\EmployeeDayWorkTime;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class EmployeeDay extends FluxModel implements InteractsWithDataTables
{
    use Commentable, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_holiday' => 'boolean',
            'is_work_day' => 'boolean',
        ];
    }

    public function absenceRequests(): BelongsToMany
    {
        return $this->belongsToMany(AbsenceRequest::class, 'absence_request_employee_day')
            ->using(AbsenceRequestEmployeeDay::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getAvatarUrl(): ?string
    {
        return $this->employee->getAvatarUrl();
    }

    public function getDescription(): ?string
    {
        $parts = [];

        if ($this->target_hours > 0) {
            $parts[] = __('Target: :hours h', ['hours' => $this->target_hours]);
        }

        if ($this->actual_hours > 0) {
            $parts[] = __('Actual: :hours h', ['hours' => $this->actual_hours]);
        }

        return implode(' | ', $parts) ?: null;
    }

    public function getLabel(): ?string
    {
        return $this->employee->name . ' - ' . $this->date->format('Y-m-d');
    }

    public function getUrl(): ?string
    {
        return route('human-resources.employee-days.show', $this->getKey());
    }

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }

    public function workTimes(): BelongsToMany
    {
        return $this->belongsToMany(WorkTime::class, 'employee_day_work_time')
            ->using(EmployeeDayWorkTime::class);
    }
}
