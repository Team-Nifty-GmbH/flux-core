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
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCartRuleset::class, 'getRules');
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

        $this->data['price_list_id'] ??= auth()->user()?->contact?->price_list_id
            ?? resolve_static(PriceList::class, 'default')?->id;

        if (! $this->data['price_list_id']) {
            dd(PriceList::all());
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
            ?? resolve_static(PaymentType::class, 'default')?->id;
        $this->data['session_id'] ??= session()->id();
    }
}
