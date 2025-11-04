<?php

namespace FluxErp\Actions\EmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\EmployeeDay\UpdateEmployeeDayRuleset;
use Illuminate\Validation\ValidationException;

class UpdateEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateEmployeeDayRuleset::class;
    }

    public function performAction(): EmployeeDay
    {
        $employeeDay = resolve_static(EmployeeDay::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $employeeDay->fill($this->getData());
        $employeeDay->save();

        return $employeeDay->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $employeeDay = resolve_static(EmployeeDay::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $errors = [];
        if (
            bccomp(
                bcadd(
                    $this->getData('sick_days_used') ?? $employeeDay->sick_days_used,
                    $this->getData('vacation_days_used') ?? $employeeDay->vacation_days_used
                ),
                1
            ) > 0
        ) {
            $errors += [
                'days_used' => ['Sick days used and vacation days used cannot exceed 1 day in total.'],
            ];
        }

        if (
            bccomp(
                bcadd(
                    $this->getData('sick_hours_used') ?? $employeeDay->sick_hours_used,
                    $this->getData('vacation_hours_used') ?? $employeeDay->vacation_hours_used
                ),
                24
            ) > 0
        ) {
            $errors += [
                'hours_used' => ['Sick hours used and vacation hours used cannot exceed 24 hours in total.'],
            ];
        }

        if ($this->getData('absence_requests')
            && resolve_static(AbsenceRequest::class, 'query')
                ->whereKey($this->getData('absence_requests'))
                ->where('employee_id', $employeeDay->employee_id)
                ->count() !== count($this->getData('absence_requests'))
        ) {
            $errors += [
                'absence_requests' => ['One or more of the given absence requests are invalid.'],
            ];
        }

        if ($this->getData('work_times')
            && resolve_static(WorkTime::class, 'query')
                ->whereKey($this->getData('work_times'))
                ->where('employee_id', $employeeDay->employee_id)
                ->where('is_daily_work_time', true)
                ->count() !== count($this->getData('work_times'))
        ) {
            $errors += [
                'work_times' => ['One or more of the given work times are invalid.'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('updateEmployeeDay');
        }
    }
}
