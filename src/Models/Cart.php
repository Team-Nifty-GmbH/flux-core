<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Cart extends Model
{
    use HasUuid, SoftDeletes;

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
}
