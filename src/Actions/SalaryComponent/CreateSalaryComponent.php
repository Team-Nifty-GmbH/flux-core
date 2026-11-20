<?php

namespace FluxErp\Actions\SalaryComponent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SalaryComponent;
use FluxErp\Rulesets\SalaryComponent\CreateSalaryComponentRuleset;

class CreateSalaryComponent extends FluxAction
{
    public static function models(): array
    {
        return [SalaryComponent::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateSalaryComponentRuleset::class;
    }

    public function performAction(): SalaryComponent
    {
        $salaryComponent = app(SalaryComponent::class, ['attributes' => $this->data]);
        $salaryComponent->save();

        return $salaryComponent->refresh();
    }
}
