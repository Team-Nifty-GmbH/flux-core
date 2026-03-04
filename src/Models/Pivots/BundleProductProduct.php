<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BundleProductProduct extends FluxPivot
{
    protected $table = 'bundle_product_product';

    protected static function booted(): void
    {
        static::created(function (BundleProductProduct $model): void {
            if (! $model->siblings()->whereKeyNot($model->id)->exists()) {
                $model->product->update([
                    'is_bundle' => true,
                ]);
            }
        });

        static::deleted(function (BundleProductProduct $model): void {
            if ($model->siblings()->count() === 0) {
                $model->product->update([
                    'is_bundle' => false,
                ]);
            }
        });
    }

    public function bundleProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bundle_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(BundleProductProduct::class, 'product_id', 'product_id');
    }
}
