<?php

namespace FluxErp\Actions\EmployeeDepartment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Rulesets\EmployeeDepartment\UpdateEmployeeDepartmentRuleset;
use Illuminate\Validation\Rule;

class UpdateEmployeeDepartment extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDepartment::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateEmployeeDepartmentRuleset::class;
    }

    public function performAction(): EmployeeDepartment
    {
        $employeeDepartment = resolve_static(EmployeeDepartment::class, 'query')
            ->whereKey($this->data['id'])
            ->firstOrFail();

        $employeeDepartment->fill($this->getData());
        $employeeDepartment->save();

        return $employeeDepartment->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['code'] = [
            'sometimes',
            'nullable',
            'string',
            'max:50',
            Rule::unique('employee_departments', 'code')
                ->where('client_id', data_get($this->data, 'client_id', auth()->user()?->client_id ?? 1))
                ->ignore(data_get($this->data, 'id')),
        ];
    }
}
