<?php

namespace FluxErp\Actions\Cart;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Cart;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Rulesets\Cart\CreateCartRuleset;

class CreateCart extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateCartRuleset::class;
    }

    public static function models(): array
    {
        return [Cart::class];
    }

    public function performAction(): mixed
    {
        // allow setting of portal public and public flags only for users
        if (! auth()->user() instanceof User) {
            unset($this->data['is_portal_public'], $this->data['is_public']);
        }

        $cart = app(Cart::class, ['attributes' => $this->data]);
        $cart->save();

        return $cart->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['authenticatable_type'] ??= auth()->user()?->getMorphClass();
        $this->data['authenticatable_id'] ??= auth()?->id();
        $this->data['payment_type_id'] ??= auth()->user()?->contact?->payment_type_id
            ?? PaymentType::default()?->id;
        $this->data['price_list_id'] ??= auth()->user()?->contact?->price_list_id
            ?? PriceList::default()?->id;
        $this->data['session_id'] ??= session()->id();
    }
}
