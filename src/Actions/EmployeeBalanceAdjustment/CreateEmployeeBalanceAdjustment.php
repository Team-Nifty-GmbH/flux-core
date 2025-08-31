<?php

namespace FluxErp\Actions\EmployeeBalanceAdjustment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeBalanceAdjustment;
use FluxErp\Rulesets\EmployeeBalanceAdjustment\CreateEmployeeBalanceAdjustmentRuleset;

class CreateEmployeeBalanceAdjustment extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeBalanceAdjustment::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEmployeeBalanceAdjustmentRuleset::class;
    }

    public function performAction(): EmployeeBalanceAdjustment
    {
        $employeeBalanceAdjustment = app(EmployeeBalanceAdjustment::class, ['attributes' => $this->data]);
        $employeeBalanceAdjustment->save();

        return $employeeBalanceAdjustment->fresh();
    }
}
