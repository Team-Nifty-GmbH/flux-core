<?php

namespace FluxErp\Actions\CartItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CartItem;
use FluxErp\Rulesets\CartItem\DeleteCartItemRuleset;

class DeleteCartItem extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteCartItemRuleset::class;
    }

    public static function models(): array
    {
        return [CartItem::class];
    }

    public function performAction(): mixed
    {
        return resolve_static(CartItem::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
