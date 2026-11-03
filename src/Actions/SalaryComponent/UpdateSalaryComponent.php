<?php

namespace FluxErp\Actions\SalaryComponent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SalaryComponent;
use FluxErp\Rulesets\SalaryComponent\UpdateSalaryComponentRuleset;

class UpdateSalaryComponent extends FluxAction
{
    public static function models(): array
    {
        return [SalaryComponent::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateSalaryComponentRuleset::class;
    }

    public function performAction(): SalaryComponent
    {
        $salaryComponent = resolve_static(SalaryComponent::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $salaryComponent->fill($this->getData());
        $salaryComponent->save();

        return $salaryComponent->withoutRelations()->fresh();
    }
}
