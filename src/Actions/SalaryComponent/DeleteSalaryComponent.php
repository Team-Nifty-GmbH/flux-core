<?php

namespace FluxErp\Actions\SalaryComponent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SalaryComponent;
use FluxErp\Rulesets\SalaryComponent\DeleteSalaryComponentRuleset;

class DeleteSalaryComponent extends FluxAction
{
    public static function models(): array
    {
        return [SalaryComponent::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteSalaryComponentRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(SalaryComponent::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
