<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Livewire\Forms\CartForm;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use WireUi\Traits\Actions;

class WatchlistCard extends Component
{
    use Actions;

    public CartForm $cartForm;

    public function mount(Cart $cart): void
    {
        $cart->loadMissing('cartItems');

        $this->cartForm->fill($cart);
    }

    public function render(): View
    {
        return view('flux::livewire.portal.shop.watchlist-card');
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

    #[Renderless]
    public function addToCart(): void
    {
        $this->dispatch(
            'cart:add',
            products: resolve_static(Cart::class, 'query')
                ->whereKey($this->cartForm->id)
                ->first()
                ->cartItems()
                ->whereHas('product', function (Builder $query) {
                    $query->when(auth()->user()?->getMorphClass() !== 'user', fn () => $query->webshop());
                })
                ->with([
                    'product' => fn (BelongsTo $query) => $query
                        ->when(auth()->user()?->getMorphClass() !== 'user', fn () => $query->webshop()),
                ])
                ->get(['product_id', 'amount'])
                ->map(fn (CartItem $cartItem) => ['id' => $cartItem->product_id, 'amount' => $cartItem->amount])
                ->toArray()
        )
            ->to(auth()->user()?->getMorphClass() === 'user' ? 'cart.cart' : 'portal.shop.cart');
    }

    public function updatedCartFormIsPublic(): void
    {
        $this->skipRender();
        $this->cartForm->save();
    }

    public function updatedCartFormIsPortalPublic(): void
    {
        $this->skipRender();
        $this->cartForm->save();
    }
}
