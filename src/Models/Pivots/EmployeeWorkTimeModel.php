<?php

namespace FluxErp\Models\Pivots;

use Carbon\Carbon;
use FluxErp\Models\Employee;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkTimeModel extends FluxPivot
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    public $incrementing = true;

    public $primaryKey = 'pivot_id';

    protected $table = 'employee_work_time_models';

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalVacationDays(?Carbon $start = null, ?Carbon $end = null): string
    {
        $annualVacationDays = $this->annual_vacation_days ?? $this->workTimeModel->annual_vacation_days ?? 0;

        $end ??= $this->valid_until ?? now()->endOfYear();
        $start ??= $this->valid_from->copy();

        if ($this->valid_from->isAfter($start)) {
            $start = $this->valid_from->copy();
        }

        if ($this->valid_until && $this->valid_until->isBefore($end)) {
            $end = $this->valid_until->copy();
        }

        if ($end->isBefore($start)) {
            return 0;
        }

        $totalYearsValid = bcround($start->diffInYears($end->addDay()), 2);
        $totalVacationDays = bcround(bcmul($totalYearsValid, $annualVacationDays));

        return bcround($totalVacationDays);
    }

    public function workTimeModel(): BelongsTo
    {
        return $this->belongsTo(WorkTimeModel::class);
    }
}
