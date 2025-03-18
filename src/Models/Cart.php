<?php

namespace FluxErp\Models;

use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class Cart extends FluxModel
{
    use HasPackageFactory, HasUuid, SoftDeletes;

    public function addItems(array|int $products): static
    {
        $products = Arr::wrap(is_array($products) && ! array_is_list($products) ? [$products] : $products);

        foreach ($products as $product) {
            $productModel = null;
            if ($productId = is_array($product) ? data_get($product, 'id') : $product) {
                $productModel = resolve_static(Product::class, 'query')
                    ->whereKey($productId)
                    ->first();
            }

            $data = [
                'cart_id' => $this->id,
                'product_id' => data_get($product, 'id', $productModel?->id),
                'name' => data_get($product, 'name', $productModel?->name),
                'amount' => $product['amount'] ?? 1,
                'price' => $product['price']
                    ?? PriceHelper::make($productModel)
                        ->when(
                            auth()->user() instanceof Address,
                            fn (PriceHelper $price) => $price->setContact(auth()->user()->contact)
                        )
                        ->price()
                        ?->price,
            ];

            if ($cartItem = $this->cartItems()->where('product_id', $productId)->first()) {
                $data['id'] = $cartItem->id;
                $data['amount'] = bcadd($cartItem->amount, $data['amount']);

                $action = UpdateCartItem::make($data);
            } else {
                $action = CreateCartItem::make($data);
            }

            $action->checkPermission()->validate()->execute();
        }

        return $this;
    }

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function createOrder(
        ?Address $address = null,
        Address|Arrayable|array|null $deliveryAddress = null,
        ?array $attributes = null
    ): Order {
        if (is_null($address)) {
            $address = $this->authenticatable ?? auth()->user();

            if (! $address instanceof Address) {
                throw new InvalidArgumentException('Address must be an instance of ' . Address::class);
            }
        }

        $order = CreateOrder::make(
            array_merge(
                $attributes ?? [],
                [
                    'order_type_id' => OrderType::query()
                        ->where('order_type_enum', 'order')
                        ->first()
                        ->id,
                    'contact_id' => $address->contact_id,
                    'client_id' => $address->contact->client_id,
                    'is_imported' => true,
                    'address_delivery' => is_array($deliveryAddress) || is_null($deliveryAddress)
                        ? $deliveryAddress
                        : $deliveryAddress->toArray(),
                ]
            )
        )
            ->validate()
            ->execute();

        foreach ($this->cartItems as $cartItem) {
            CreateOrderPosition::make([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'amount' => $cartItem->amount,
                'unit_price' => $cartItem->price,
            ])
                ->validate()
                ->execute();
        }

        $order->calculatePrices()->save();

        return $order;
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            CartItem::class,
            'cart_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function scopeCurrent(Builder $query): void
    {
        $query->where('is_watchlist', false)->latest();
    }

    public function vatRates(): Collection
    {
        return $this->cartItems()
            ->with('vatRate:id,rate_percentage')
            ->select('vat_rate_id')
            ->selectRaw('SUM(total_net) as total_net_sum')
            ->selectRaw('SUM(total_gross) as total_gross_sum')
            ->selectRaw('(SUM(total_gross) - SUM(total_net)) as vat_sum')
            ->groupBy('vat_rate_id')
            ->get()
            ->toBase()
            ->transform(function (CartItem $item) {
                return array_merge(
                    $item->toArray(),
                    [
                        'vat_rate_percentage' => $item->vatRate->rate_percentage,
                    ]
                );
            });
    }
}
