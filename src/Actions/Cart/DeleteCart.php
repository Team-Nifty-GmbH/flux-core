<?php

namespace FluxErp\Actions\Cart;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Cart;
use FluxErp\Rulesets\Cart\DeleteCartRuleset;

class DeleteCart extends FluxAction
{
    protected static bool $hasPermission = false;

    protected function getRulesets(): string|array
    {
        return DeleteCartRuleset::class;
    }

    public static function models(): array
    {
        return [Cart::class];
    }

    public function performAction(): mixed
    {
        return resolve_static(Cart::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
