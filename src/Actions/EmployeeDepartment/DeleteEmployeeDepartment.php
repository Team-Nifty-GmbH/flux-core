<?php

namespace FluxErp\Actions\EmployeeDepartment;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Rulesets\EmployeeDepartment\DeleteEmployeeDepartmentRuleset;

class DeleteEmployeeDepartment extends FluxAction
{
    public static function models(): array
    {
        return [EmployeeDepartment::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteEmployeeDepartmentRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(EmployeeDepartment::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
