<?php

namespace FluxErp\Actions\CartItem;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\PriceHelper;
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
        $this->data['amount'] ??= 1;

        $product = null;
        if ($productId = data_get($this->data, 'product_id')) {
            $product = Product::query()
                ->whereKey($productId)
                ->first(['vat_rate_id', 'name']);
            $this->data['vat_rate_id'] ??= $product?->vat_rate_id;
            $this->data['name'] ??= $product?->name;
        }

        $cart = resolve_static(Cart::class, 'query')            ->with('authenticatable')
            ->whereKey($this->data['cart_id'])
            ->sole();

        if (
            $cart->authenticatable instanceof Address
            && ! data_get($this->data, 'price')
            && $product
        ) {
            $this->data['price'] = PriceHelper::make($product)
                ->setContact($cart->authenticatable->contact)
                ->price()
                ->price;
        }

        $cartItem = app(CartItem::class, ['attributes' => $this->data]);
        $cartItem->save();

        return $cartItem->fresh();
    }
}
