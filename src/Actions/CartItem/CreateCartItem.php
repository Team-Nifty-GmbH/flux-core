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
    protected Cart $cart;

    protected ?Product $product = null;

    protected function getRulesets(): string|array
    {
        return CreateCartItemRuleset::class;
    }

    public static function models(): array
    {
        return [CartItem::class];
    }

    public function performAction(): mixed
    {
        $this->data['amount'] ??= 1;

        $cartItem = app(CartItem::class, ['attributes' => $this->data]);
        $cartItem->save();

        return $cartItem->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->cart = resolve_static(Cart::class, 'query')
            ->with('authenticatable')
            ->whereKey($this->data['cart_id'])
            ->sole();

        if ($productId = data_get($this->data, 'product_id')) {
            $this->product = Product::query()
                ->whereKey($productId)
                ->first(['vat_rate_id', 'name']);
            $this->data['vat_rate_id'] ??= $this->product?->vat_rate_id;
            $this->data['name'] ??= $this->product?->name;
        }

        if (
            $this->cart->authenticatable instanceof Address
            && is_null(data_get($this->data, 'price'))
            && $this->product
        ) {
            $this->data['price'] = PriceHelper::make($this->product)
                ->setContact($this->cart->authenticatable->contact)
                ->price()
                ?->price;
        }
    }
}
