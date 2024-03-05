<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class ProductBundleProduct extends Pivot
{
    use BroadcastsEvents;

    protected $table = 'product_bundle_product';

    public $timestamps = false;

    public $incrementing = true;

    protected static function booted(): void
    {
        static::created(function (ProductBundleProduct $model) {
            if (! $model->siblings()->whereKeyNot($model->id)->exists()) {
                $model->product->update([
                    'is_bundle' => true,
                ]);
            }
        });

        static::deleted(function (ProductBundleProduct $model) {
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
        return $this->hasMany(ProductBundleProduct::class, 'product_id', 'product_id');
    }
}
