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

    protected function getRulesets(): string|array
    {
        return DeleteEmployeeDayRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(EmployeeDay::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
