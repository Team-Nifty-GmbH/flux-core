<?php

namespace FluxErp\Livewire;

use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Address;
use FluxErp\Models\Cart as CartModel;
use FluxErp\Models\CartItem;
use FluxErp\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
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

    #[Locked]
    public ?int $cartId = null;

    public function mount(): void
    {
        $this->getWatchLists();
        $this->watchlists[] = ['id' => 0, 'name' => __('New watchlist')];
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . $this->cart()->broadcastChannel() . ',.CartUpdated' => 'refresh',
            'echo-private:' . app(CartModel::class)->broadcastChannel()
                . $this->cartId . ',.CartDeleted' => 'refresh',
            'cart:add' => 'add',
            'cart:remove' => 'remove',
            'cart:refresh' => 'refresh',
        ];
    }

    public function render(): View
    {
        return view('flux::livewire.cart');
    }

    public function refresh(): void
    {
        unset($this->cart);
    }

    public function add(array|int $products): void
    {
        $products = Arr::wrap(is_array($products) && ! array_is_list($products) ? [$products] : $products);

        foreach ($products as $product) {
            if ($productId = is_array($product) ? data_get($product, 'id') : $product) {
                $productModel = resolve_static(Product::class, 'query')
                    ->whereKey($productId)
                    ->first();
            }

            $data = [
                'cart_id' => $this->cart()->id,
                'product_id' => data_get($product, 'id', $productModel?->id),
                'name' => data_get($product, 'name', $productModel?->name),
                'amount' => $product['amount'] ?? 1,
                'price' => $product['price']
                    ?? PriceHelper::make($productModel)
                        ->when(
                            auth()->user() instanceof Address,
                            fn ($price) => $price->setContact(auth()->user()->contact)
                        )
                        ->price()
                        ->price,
            ];

            // check if a product with the same id is already in the cart
            if ($cartItem = $this->cart()->cartItems()->where('product_id', $productId)->first()) {
                $data['id'] = $cartItem->id;
                $data['amount'] = bcadd($cartItem->amount, $data['amount']);

                $action = UpdateCartItem::make($data);
            } else {
                $action = CreateCartItem::make($data);
            }

            try {
                $action->validate()->execute();
            } catch (ValidationException $e) {
                exception_to_notifications($e, $this);

                return;
            }

            unset($this->cart);

        }

        $this->notification()->success(count($products) > 1
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
            $this->cart()->cartItems()->select(['product_id', 'amount'])->get()->toArray()
        );
    }

    public function updatedLoadWatchlist(): void
    {
        if (is_null($this->loadWatchlist)) {
            return;
        }

        $this->add(
            app(CartModel::class)
                ->with('products')
                ->whereKey($this->loadWatchlist)
                ->first()
                ->products
                ->pluck('id')
                ->toArray()
        );

        $this->loadWatchlist = null;
    }

    #[Computed(persist: true)]
    public function cart(): ?CartModel
    {
        $cart = cart();
        $this->cartId = $cart->id;

        return $cart;
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
