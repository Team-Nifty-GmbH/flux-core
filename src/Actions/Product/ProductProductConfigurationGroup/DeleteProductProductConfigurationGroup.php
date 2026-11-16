<?php

namespace FluxErp\Actions\Product\ProductProductConfigurationGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Rulesets\Product\ProductProductConfigurationGroup\DeleteProductProductConfigurationGroupRuleset;

class DeleteProductProductConfigurationGroup extends FluxAction
{
    public static function models(): array
    {
        return [ProductProductConfigurationGroup::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductProductConfigurationGroupRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(ProductProductConfigurationGroup::class, 'query')
            ->whereKey($this->getData('pivot_id'))
            ->first()
            ->delete();
    }
}
