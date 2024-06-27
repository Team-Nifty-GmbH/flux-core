<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Livewire\Cart as BaseCart;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;

class Cart extends BaseCart
{
    public function render(): View
    {
        return view('flux::livewire.portal.shop.cart');
    }

    #[Renderless]
    public function saveToWatchlist(): bool
    {
        try {
            if ($this->selectedWatchlist) {
                $cart = app(\FluxErp\Models\Cart::class)
                    ->query()
                    ->whereKey($this->selectedWatchlist)
                    ->where('is_watchlist', true)
                    ->where('is_portal_public', false)
                    ->first();
            } else {
                $this->validate();
                $cart = CreateCart::make([
                    'name' => $this->watchlistName,
                    'is_watchlist' => true,
                ])
                    ->validate()
                    ->execute();
            }

            foreach ($this->cart()->cartItems as $item) {
                CreateCartItem::make(array_merge($item->toArray(), ['cart_id' => $cart->id]))
                    ->validate()
                    ->execute();
            }
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->reset('selectedWatchlist', 'watchlistName');
        $this->notification()->success(__('Cart saved to watchlist'));

        return true;
    }

    protected function getWatchLists(): void
    {
        $this->watchlists = app(\FluxErp\Models\Cart::class)
            ->query()
            ->where('authenticatable_type', auth()->user()->getMorphClass())
            ->where('authenticatable_id', auth()->id())
            ->where('is_watchlist', true)
            ->select(['id', 'name'])
            ->get()
            ->toArray();
    }
}
