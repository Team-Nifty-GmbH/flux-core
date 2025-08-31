<?php

namespace FluxErp\Actions\Employee;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Employee;
use FluxErp\Rulesets\Employee\DeleteEmployeeRuleset;

class DeleteEmployee extends FluxAction
{
    public static function models(): array
    {
        return [Employee::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteEmployeeRuleset::class;
    }

    public function performAction(): ?bool
    {
        $employee = resolve_static(Employee::class, 'query')->whereKey($this->data['id'])->firstOrFail();

        return $employee->delete();
    }
}
