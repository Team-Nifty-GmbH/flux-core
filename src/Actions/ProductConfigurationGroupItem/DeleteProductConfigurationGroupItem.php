<?php

namespace FluxErp\Actions\ProductConfigurationGroupItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Rulesets\ProductConfigurationGroupItem\DeleteProductConfigurationGroupItemRuleset;

class DeleteProductConfigurationGroupItem extends FluxAction
{
    public static function models(): array
    {
        return [ProductConfigurationGroupItem::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductConfigurationGroupItemRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(ProductConfigurationGroupItem::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
