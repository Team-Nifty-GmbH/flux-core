<?php

namespace FluxErp\Actions\CartItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Product;
use FluxErp\Rulesets\CartItem\CreateCartItemRuleset;

class CreateCartItem extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateCartItemRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [CartItem::class];
    }

    public function performAction(): mixed
    {
        $this->data['vat_rate_id'] ??= app(Product::class)
            ->query()
            ->whereKey($this->data['product_id'])
            ->value('vat_rate_id');
        $cart = app(Cart::class)
            ->query()
            ->whereKey($this->data['cart_id'])
            ->with('authenticatable.contact.priceList')
            ->sole();

        if ($cart->authenticatable instanceof Address) {
            $this->data['is_net'] ??= $cart->authenticatable->contact->priceList->is_net;
        }

        $cart = app(CartItem::class, ['attributes' => $this->data]);
        $cart->save();

        return $cart->fresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['amount'] ??= 1;
    }
}
