<?php

namespace FluxErp\Actions\EmployeeDepartment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Rulesets\EmployeeDepartment\CreateEmployeeDepartmentRuleset;
use Illuminate\Validation\Rule;

class CreateEmployeeDepartment extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDepartment::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEmployeeDepartmentRuleset::class;
    }

    public function performAction(): EmployeeDepartment
    {
        $employeeDepartment = app(EmployeeDepartment::class, ['attributes' => $this->getData()]);
        $employeeDepartment->save();

        return $employeeDepartment->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['code'] = [
            'nullable',
            'string',
            'max:50',
            Rule::unique('employee_departments', 'code')
                ->where('client_id', data_get($this->data, 'client_id', auth()->user()?->client_id ?? 1)),
        ];
    }
}
