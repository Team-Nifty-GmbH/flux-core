<?php

namespace FluxErp\Actions\CartItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CartItem;
use FluxErp\Rulesets\CartItem\DeleteCartItemRuleset;

class DeleteCartItem extends FluxAction
{
    protected static bool $hasPermission = false;

    public static function models(): array
    {
        return [CartItem::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteCartItemRuleset::class;
    }

    public function performAction(): mixed
    {
        return resolve_static(CartItem::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
