<?php

namespace FluxErp\Actions\Employee;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Employee;
use FluxErp\Rulesets\Employee\CreateEmployeeRuleset;

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
        $employee = app(Employee::class, ['attributes' => $this->data]);
        $employee->save();

        return $employee->fresh();
    }
}
