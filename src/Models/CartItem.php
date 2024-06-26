<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
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
