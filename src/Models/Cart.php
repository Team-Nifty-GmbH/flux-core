<?php

namespace FluxErp\Models;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Cart extends FluxModel
{
    use BroadcastsEvents, HasPackageFactory, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
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

    public function broadcastWith(): array
    {
        return ['id' => $this->id];
    }

    public function createOrder(
        ?Address $address = null,
        Address|Arrayable|array|null $deliveryAddress = null,
        ?array $attributes = null
    ): Order {
        if (is_null($address)) {
            $address = $this->authenticatable ?? auth()->user();

            if (! $address instanceof Address) {
                throw new \InvalidArgumentException('Address must be an instance of ' . Address::class);
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
}
