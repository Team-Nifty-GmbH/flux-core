<?php

namespace FluxErp\Actions\Employee;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Tenant;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rulesets\Employee\CreateEmployeeRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateEmployee extends FluxAction
{
    public static function models(): array
    {
        return [Employee::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEmployeeRuleset::class;
    }

    public function performAction(): Employee
    {
        $data = $this->getData();
        $workTimeModel = Arr::pull($data, 'work_time_model_id');

        $employee = app(Employee::class, ['attributes' => $data]);
        $employee->save();

        if ($workTimeModel) {
            AssignWorkTimeModel::make([
                'employee_id' => $employee->getKey(),
                'work_time_model_id' => $workTimeModel,
                'valid_from' => $employee->employment_date,
            ])
                ->validate()
                ->execute();
        }

        return $employee->refresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')
            ?->getKey();
        $this->data['vacation_carryover_rule_id'] ??= resolve_static(
            VacationCarryoverRule::class,
            'default'
        )
            ?->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (
            $this->getData('employee_department_id')
            && $this->getData('location_id')
            && resolve_static(EmployeeDepartment::class, 'query')
                ->whereKey($this->getData('employee_department_id'))
                ->where('location_id', $this->getData('location_id'))
                ->doesntExist()
        ) {
            throw ValidationException::withMessages([
                'employee_department_id' => ['The selected department is not available for the selected location.'],
            ])
                ->errorBag('createEmployee');
        }
    }
}
