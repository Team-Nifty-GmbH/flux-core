<?php

namespace FluxErp\Actions\CartItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CartItem;
use FluxErp\Rulesets\CartItem\UpdateCartItemRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCartItem extends FluxAction
{
    protected static bool $hasPermission = false;

    public static function models(): array
    {
        return [CartItem::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateCartItemRuleset::class;
    }

    public function performAction(): Model
    {
        $cartItem = resolve_static(CartItem::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $cartItem->fill($this->data);
        $cartItem->save();

        return $cartItem->withoutRelations()->fresh();
    }
}
