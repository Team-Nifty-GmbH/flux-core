<?php

namespace FluxErp\Livewire;

use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Address;
use FluxErp\Models\CartItem;
use FluxErp\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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

    protected $listeners = [
        'cart:add' => 'add',
        'cart:remove' => 'remove',
        'cart:refresh' => 'refresh',
    ];

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
        foreach (Arr::wrap($products) as $product) {
            if ($productId = is_array($product) ? data_get($product, 'id') : $product) {
                $productModel = app(Product::class)->whereKey($productId)->first();
                if (is_null($productModel)) {
                    dd($productModel, $product, $productId);
                }
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
                $data['amount'] = bcadd($cartItem->amount, $data['amount']);
                $data['id'] = $cartItem->id;

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

            $this->notification()->success(__('Product added to cart'));
        }
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
                $cart = app(\FluxErp\Models\Cart::class)
                    ->query()
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

        $this->loadCart(
            app(\FluxErp\Models\Cart::class)->with('products')->whereKey($this->loadWatchlist)->first()
        );
        $this->loadWatchlist = null;
    }

    public function loadCart(\FluxErp\Models\Cart $cart): void
    {
        foreach ($cart->products as $product) {
            $this->add($product->id);
        }
    }

    #[Computed(persist: true)]
    public function cart(): ?\FluxErp\Models\Cart
    {
        return cart();
    }

    protected function getWatchLists(): void
    {
        $this->watchlists = app(\FluxErp\Models\Cart::class)
            ->query()
            ->where(function (Builder $query) {
                $query->where(fn (Builder $query) => $query->where('authenticatable_type', auth()->user()->getMorphClass())
                    ->where('authenticatable_id', auth()->id()))
                    ->orWhere('is_public', true);
            })
            ->where('is_watchlist', true)
            ->select(['id', 'name'])
            ->get()
            ->toArray();
    }
}
