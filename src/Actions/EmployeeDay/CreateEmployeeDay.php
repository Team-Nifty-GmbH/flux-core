<?php

namespace FluxErp\Actions\EmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\EmployeeDay\CreateEmployeeDayRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEmployeeDayRuleset::class;
    }

    public function performAction(): EmployeeDay
    {
        $data = $this->getData();
        $absenceRequests = Arr::pull($data, 'absence_requests');
        $workTimes = Arr::pull($data, 'work_times');

        $employeeDay = app(EmployeeDay::class, ['attributes' => $this->getData()]);
        $employeeDay->save();

        if ($absenceRequests) {
            $employeeDay->absenceRequests()->attach($absenceRequests);
        }

        if ($workTimes) {
            $employeeDay->workTimes()->attach($workTimes);
        }

        return $employeeDay->refresh();
    }

    protected function prepareForValidation(): void
    {
        if ($this->getData('employee_id')) {
            $this->mergeRules([
                'absence_requests.*' => [
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => AbsenceRequest::class])
                        ->where('employee_id', $this->getData('employee_id')),
                ],
                'work_times.*' => [
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => WorkTime::class])
                        ->where('employee_id', $this->getData('employee_id'))
                        ->where('is_daily_work_time', true),
                ],
            ]);
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        if (
            bccomp(
                bcadd(
                    $this->getData('sick_days_used') ?? 0,
                    $this->getData('vacation_days_used') ?? 0
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
                    $this->getData('sick_hours_used') ?? 0,
                    $this->getData('vacation_hours_used') ?? 0
                ),
                24
            ) > 0
        ) {
            $errors += [
                'hours_used' => ['Sick hours used and vacation hours used cannot exceed 24 hours in total.'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('createEmployeeDay');
        }
    }
}
