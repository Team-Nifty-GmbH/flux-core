<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends FluxModel
{
    use HasUuid;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::saving(function (CartItem $cartItem) {
            $cartItem->loadMissing([
                'cart.priceList:id,is_net',
                'vatRate:id,rate_percentage',
            ]);

            $cartItem->total = bcmul($cartItem->amount, $cartItem->price);
            $cartItem->total_net = $cartItem->cart->priceList->is_net
                ? $cartItem->total
                : gross_to_net($cartItem->total, $cartItem->vatRate->rate_percentage);
            $cartItem->total_gross = $cartItem->cart->priceList->is_net
                ? net_to_gross($cartItem->total, $cartItem->vatRate->rate_percentage)
                : $cartItem->total;
        });

        static::saved(function (CartItem $cartItem) {
            // Update the cart's updated_at timestamp to reflect the change in the cart item
            // it also triggers the broadcasting of the cart update
            $cartItem->cart->touch();
        });
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }
}
