<?php

namespace FluxErp\Actions\CartItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CartItem;
use FluxErp\Rulesets\CartItem\DeleteCartItemRuleset;

class DeleteCartItem extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCartItemRuleset::class, 'getRules');
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
