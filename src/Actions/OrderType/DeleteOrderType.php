<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\DeleteOrderTypeRuleset;

class DeleteOrderType extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteOrderTypeRuleset::class;
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(OrderType::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
