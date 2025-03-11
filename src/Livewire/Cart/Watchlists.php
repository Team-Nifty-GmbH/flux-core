<?php

namespace FluxErp\Livewire\Cart;

use FluxErp\Livewire\Portal\Shop\Watchlists as BaseWatchlist;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Watchlists extends BaseWatchlist
{
    public function render(): View
    {
        return view(
            'flux::livewire.cart.watchlist',
            [
                'carts' => $this->getCarts(),
            ]
        );
    }

    public function addToCart(Cart $cart): void
    {
        $this->dispatch(
            event: 'cart:add',
            products: $cart->cartItems()
                ->ordered()
                ->get(['product_id', 'amount', 'order_column'])
                ->map(fn (CartItem $cartItem) => ['id' => $cartItem->product_id, 'amount' => $cartItem->amount])
                ->toArray()
        )
            ->to('cart.cart');
    }

    public function getCarts(): Collection
    {
        return resolve_static(Cart::class, 'query')
            ->where('is_watchlist', true)
            ->where(function (Builder $query): void {
                $query->where(fn (Builder $query) => $query
                    ->where('authenticatable_id', auth()->id())
                    ->where('authenticatable_type', auth()->user()?->getMorphClass())
                )
                    ->orWhere('is_public', true);
            })
            ->with([
                'cartItems' => fn (HasMany $query) => $query->ordered(),
                'cartItems.product',
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
