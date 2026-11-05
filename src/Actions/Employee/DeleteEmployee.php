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
        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        // Free the user for a later link, the work times keep their employee.
        $employee->user_id = null;
        $employee->saveQuietly();

        return $employee->delete();
    }
}
