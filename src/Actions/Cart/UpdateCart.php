<?php

namespace FluxErp\Actions\Cart;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Cart;
use FluxErp\Rulesets\Cart\UpdateCartRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCart extends FluxAction
{
    protected static bool $hasPermission = false;

    public static function models(): array
    {
        return [Cart::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateCartRuleset::class;
    }

    public function performAction(): Model
    {
        $cart = resolve_static(Cart::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $cart->fill($this->data);
        $cart->save();

        return $cart->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['authenticatable_type'] ??= auth()->user()?->getMorphClass();
        $this->data['authenticatable_id'] ??= auth()->user()?->getKey();
        $this->data['session_id'] ??= session()->id();
    }
}
