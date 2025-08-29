<?php

namespace FluxErp\Actions\EmployeeDay;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeDay;
use FluxErp\Rulesets\EmployeeDay\CreateEmployeeDayRuleset;

class CreateEmployeeDay extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDay::class];
    }

    public static function name(): string
    {
        return 'employee-day.create';
    }

    protected function getRulesets(): string|array
    {
        return CreateEmployeeDayRuleset::class;
    }

    public function performAction(): EmployeeDay
    {
        $employeeDay = app(EmployeeDay::class, ['attributes' => $this->getData()]);
        $employeeDay->save();

        return $employeeDay->fresh();
    }
}
