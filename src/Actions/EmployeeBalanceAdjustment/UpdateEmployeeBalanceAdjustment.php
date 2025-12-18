<?php

namespace FluxErp\Actions\EmployeeBalanceAdjustment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeBalanceAdjustment;
use FluxErp\Rulesets\EmployeeBalanceAdjustment\UpdateEmployeeBalanceAdjustmentRuleset;

class UpdateEmployeeBalanceAdjustment extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeBalanceAdjustment::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateEmployeeBalanceAdjustmentRuleset::class;
    }

    public function performAction(): EmployeeBalanceAdjustment
    {
        $employeeBalanceAdjustment = resolve_static(EmployeeBalanceAdjustment::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $employeeBalanceAdjustment->fill($this->getData());
        $employeeBalanceAdjustment->save();

        return $employeeBalanceAdjustment->withoutRelations()->fresh();
    }
}
