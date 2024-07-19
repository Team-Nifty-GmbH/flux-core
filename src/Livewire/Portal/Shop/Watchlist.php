<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Actions\Cart\DeleteCart;
use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Watchlist extends Component
{
    public function render(): View
    {
        // Watchlists are only allowed for authenticated users
        return view(
            'flux::livewire.portal.shop.watchlist',
            [
                'carts' => app(Cart::class)
                    ->query()
                    ->where('is_watchlist', true)
                    ->with([
                        'products' => fn (HasManyThrough $query) => $query->webshop(),
                        'products.coverMedia',
                        'products.parent.coverMedia',
                    ])
                    ->get()
                    ->filter(fn (Cart $cart) => $cart->products->isNotEmpty())
                    ->each(fn (Cart $cart) => $cart->products
                        ?->transform(function (Product $product) {
                            $product->append('price');
                            $productArray = $product->toArray();
                            $productArray['cover_url'] = ($product->coverMedia ?? $product->parent?->coverMedia)
                                ?->getUrl('thumb_280x280') ?? route('icons', ['name' => 'photo', 'variant' => 'outline']);
                            $productArray['price'] = $product->price->only([
                                'price',
                                'root_price_flat',
                                'root_discount_percentage',
                            ]);

                            return $productArray;
                        })
                    ),
            ]
        );
    }

    public function removeProduct(Cart $cart, int $productId): void
    {
        try {
            DeleteCartItem::make([
                'id' => app(CartItem::class)
                    ->query()
                    ->where('cart_id', $cart->id)
                    ->where('product_id', $productId)
                    ->value('id'),
            ])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            $this->skipRender();
        }
    }

    public function deleteCart(Cart $cart): void
    {
        try {
            DeleteCart::make([
                'id' => $cart->id,
            ])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            $this->skipRender();
        }
    }

    public function addToCart(Cart $cart): void
    {
        $this->dispatch('cart:add', $cart->products->pluck('id'))->to('portal.shop.cart');
    }
}
