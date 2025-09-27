<?php

namespace FluxErp\Actions\Employee;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Employee;
use FluxErp\Rulesets\Employee\UpdateEmployeeRuleset;

class UpdateEmployee extends FluxAction
{
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
}
