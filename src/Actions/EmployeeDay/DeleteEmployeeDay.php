<?php

namespace FluxErp\Actions\EmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rulesets\EmployeeDay\DeleteEmployeeDayRuleset;

class DeleteEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    public static function name(): string
    {
        return 'employee-day.delete';
    }

    protected function getRulesets(): string|array
    {
        return DeleteEmployeeDayRuleset::class;
    }

    public function performAction(): ?bool
    {
        $employeeDay = resolve_static(EmployeeDay::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        return $employeeDay->delete();
    }
}
