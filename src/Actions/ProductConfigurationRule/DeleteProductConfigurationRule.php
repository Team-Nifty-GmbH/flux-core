<?php

namespace FluxErp\Actions\ProductConfigurationRule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductConfigurationRule;
use FluxErp\Rulesets\ProductConfigurationRule\DeleteProductConfigurationRuleRuleset;

class DeleteProductConfigurationRule extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationRule::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductConfigurationRuleRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(ProductConfigurationRule::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
