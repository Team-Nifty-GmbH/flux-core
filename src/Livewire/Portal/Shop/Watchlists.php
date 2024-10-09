<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Watchlists extends Component
{
    public function render(): View
    {
        // Watchlists are only allowed for authenticated users
        return view(
            'flux::livewire.portal.shop.watchlists',
            [
                'carts' => $this->getCarts(),
            ]
        );
    }

    protected function getCarts(): Collection
    {
        return resolve_static(Cart::class, 'query')
            ->where('is_watchlist', true)
            ->with([
                'cartItems',
                'cartItems.product' => fn (BelongsTo $query) => $query->webshop(),
                'cartItems.product.coverMedia',
                'cartItems.product.parent.coverMedia',
            ])
            ->get()
            ->filter(fn (Cart $cart) => $cart->products->isNotEmpty())
            ->each(fn (Cart $cart) => $cart->cartItems
                ?->transform(function (CartItem $cartItem) {
                    $product = $cartItem->product;

                    if (! $product) {
                        return null;
                    }

                    $product->append('price');
                    $productArray = $product->toArray();
                    $productArray['cover_url'] = ($product->coverMedia ?? $product->parent?->coverMedia)
                        ?->getUrl('thumb_280x280') ?? route('icons', ['name' => 'photo', 'variant' => 'outline']);
                    $productArray['price'] = $product->price->only([
                        'price',
                        'root_price_flat',
                        'root_discount_percentage',
                    ]);
                    $productArray['amount'] = $cartItem->amount;
                    $productArray['cart_item_id'] = $cartItem->id;

                    return $productArray;
                })
            );
    }
}
