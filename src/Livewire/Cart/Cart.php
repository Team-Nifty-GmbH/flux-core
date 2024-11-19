<?php

namespace FluxErp\Livewire\Cart;

use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Models\Cart as CartModel;
use FluxErp\Models\CartItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Rule;
use Livewire\Component;
use WireUi\Traits\Actions;

#[Lazy]
class Cart extends Component
{
    use Actions;

    public array $watchlists = [];

    public int $selectedWatchlist = 0;

    public ?int $loadWatchlist = null;

    #[Rule('required_if:selectedWatchlist,0')]
    public ?string $watchlistName = null;

    public function mount(): void
    {
        $this->getWatchLists();
        $this->watchlists[] = ['id' => 0, 'name' => __('New watchlist')];
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . $this->cart()->broadcastChannel() . ',.CartUpdated' => 'refresh',
            'cart:add' => 'add',
            'cart:remove' => 'remove',
            'cart:refresh' => 'refresh',
        ];
    }

    public function render(): View
    {
        return view('flux::livewire.cart.cart');
    }

    public function refresh(): void
    {
        unset($this->cart);
    }

    public function add(array|int $products): void
    {
        try {
            $this->cart()->addItems($products);
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        unset($this->cart);

        $this->notification()->success(count(Arr::wrap($products)) > 1
            ? __('Products added to cart')
            : __('Product added to cart')
        );
    }

    public function remove(CartItem $cartItem): void
    {
        try {
            DeleteCartItem::make(['id' => $cartItem->id])->validate()->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        unset($this->cart);
    }

    public function clear(): void
    {
        foreach ($this->cart()->cartItems as $cartItem) {
            $this->remove($cartItem);
        }
    }

    public function updateAmount(CartItem $cartItem, float $amount): void
    {
        try {
            UpdateCartItem::make([
                'id' => $cartItem->id,
                'amount' => $amount,
            ])->validate()->execute();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        unset($this->cart);
    }

    #[Renderless]
    public function saveToWatchlist(): bool
    {
        try {
            if ($this->selectedWatchlist) {
                $cart = resolve_static(CartModel::class, 'query')
                    ->whereKey($this->selectedWatchlist)
                    ->where('is_watchlist', true)
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
        $this->mount();

        return true;
    }

    #[Renderless]
    public function addToCurrentOrder(): void
    {
        $this->dispatch(
            'order:add-products',
            $this->cart()->cartItems()->select(['product_id', 'amount', 'order_column'])->ordered()->get()->toArray()
        );
    }

    public function updatedLoadWatchlist(): void
    {
        if (is_null($this->loadWatchlist)) {
            return;
        }

        $this->add(
            resolve_static(CartModel::class, 'query')
                ->with([
                    'cartItems' => fn (HasMany $query) => $query->ordered()
                        ->select([
                            'id',
                            'cart_id',
                            'product_id',
                            'vat_rate_id',
                            'amount',
                            'order_column',
                        ]),
                ])
                ->whereKey($this->loadWatchlist)
                ->first()
                ->cartItems
                ->map(fn (CartItem $cartItem) => ['id' => $cartItem->product_id, 'amount' => $cartItem->amount])
                ->toArray()
        );

        $this->loadWatchlist = null;
    }

    #[Computed(persist: true)]
    public function cart(): ?CartModel
    {
        return cart();
    }

    protected function getWatchLists(): void
    {
        $this->watchlists = resolve_static(CartModel::class, 'query')
            ->where(function (Builder $query) {
                $query->where(fn (Builder $query) => $query
                    ->where('authenticatable_type', auth()->user()?->getMorphClass())
                    ->where('authenticatable_id', auth()->id()))
                    ->orWhere('is_public', true);
            })
            ->where('is_watchlist', true)
            ->get(['id', 'name'])
            ->toArray();
    }
}
