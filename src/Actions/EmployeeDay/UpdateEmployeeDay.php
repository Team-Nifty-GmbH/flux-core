<?php

namespace FluxErp\Actions\EmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rulesets\EmployeeDay\UpdateEmployeeDayRuleset;

class UpdateEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    public static function name(): string
    {
        return 'employee-day.update';
    }

    protected function getRulesets(): string|array
    {
        return UpdateEmployeeDayRuleset::class;
    }

    public function performAction(): EmployeeDay
    {
        $employeeDay = resolve_static(EmployeeDay::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $employeeDay->fill($this->getData());
        $employeeDay->save();

        return $employeeDay->fresh();
    }
}
