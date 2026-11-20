<?php

namespace FluxErp\Actions\Employee;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Rulesets\Employee\UpdateEmployeeRuleset;
use FluxErp\Traits\Action\ValidatesEmployeeUserLink;
use Illuminate\Validation\ValidationException;

class UpdateEmployee extends FluxAction
{
    use ValidatesEmployeeUserLink;

    public static function models(): array
    {
        return [Employee::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateEmployeeRuleset::class;
    }

    public function performAction(): Employee
    {
        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $employee->fill($this->getData());
        $employee->save();

        return $employee->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'employee_department_id', 'location_id', 'tenant_id', 'user_id']);

        if ($this->getData('user_id') !== $employee->user_id) {
            $this->validateUserLink((int) $employee->tenant_id, 'updateEmployee');
        }

        if (
            $this->getData('employee_department_id')
            && ($locationId = $this->getData('location_id') ?? $employee->location_id)
            && resolve_static(EmployeeDepartment::class, 'query')
                ->whereKey($this->getData('employee_department_id'))
                ->where('location_id', $locationId)
                ->doesntExist()
        ) {
            throw ValidationException::withMessages([
                'employee_department_id' => ['The selected department is not available for the selected location.'],
            ])
                ->errorBag('updateEmployee');
        } elseif (
            $this->getData('location_id')
            && $employee->employee_department_id
            && resolve_static(EmployeeDepartment::class, 'query')
                ->whereKey($employee->employee_department_id)
                ->where('location_id', $this->getData('location_id'))
                ->doesntExist()
        ) {
            throw ValidationException::withMessages([
                'location_id' => ['The selected location is not available for the given department.'],
            ])
                ->errorBag('updateEmployee');
        }
    }
}
