<?php

namespace FluxErp\Actions\EmployeeBalanceAdjustment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeBalanceAdjustment;
use FluxErp\Rulesets\EmployeeBalanceAdjustment\DeleteEmployeeBalanceAdjustmentRuleset;

class DeleteEmployeeBalanceAdjustment extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeBalanceAdjustment::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteEmployeeBalanceAdjustmentRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(EmployeeBalanceAdjustment::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
