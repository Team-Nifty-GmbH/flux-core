<?php

namespace FluxErp\Livewire\Cart;

use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Livewire\Forms\CartForm;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class WatchlistCard extends Component
{
    use Actions;

    public CartForm $cartForm;

    public function mount(Cart $cart): void
    {
        $cart->loadMissing(['cartItems' => fn (HasMany $query) => $query->ordered()]);

        $this->cartForm->fill($cart);
    }

    public function render(): View
    {
        return view('flux::livewire.cart.watchlist-card');
    }

    #[Renderless]
    public function addToCart(): void
    {
        $this->dispatch(
            'cart:add',
            products: resolve_static(Cart::class, 'query')
                ->whereKey($this->cartForm->id)
                ->first()
                ->cartItems()
                ->ordered()
                ->get(['product_id', 'amount', 'order_column'])
                ->map(fn (CartItem $cartItem) => ['id' => $cartItem->product_id, 'amount' => $cartItem->amount])
                ->toArray()
        )
            ->to('cart.cart');
    }

    public function delete(): void
    {
        $this->skipRender();

        try {
            $this->cartForm->delete();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->js(<<<'JS'
            $wire.$parent.$refresh();
        JS);
    }

    public function removeProduct(int $productId): void
    {
        $this->skipRender();

        try {
            DeleteCartItem::make([
                'id' => resolve_static(CartItem::class, 'query')
                    ->where('cart_id', $this->cartForm->id)
                    ->where('product_id', $productId)
                    ->value('id'),
            ])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->cartForm->cart_items = array_filter(
            $this->cartForm->cart_items,
            fn (array $cartItem) => $cartItem['id'] !== $productId
        );

        $this->js(<<<'JS'
            $wire.$parent.$refresh();
        JS);
    }

    #[Renderless]
    public function reOrder(CartItem $cartItem, int $index): void
    {
        if (! $this->cartForm->isUserOwned()) {
            return;
        }

        try {
            UpdateCartItem::make([
                'id' => $cartItem->getKey(),
                'order_column' => $index + 1,
            ])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function updatedCartFormIsPublic(): void
    {
        $this->skipRender();
        $this->cartForm->save();
    }
}
