<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class ProductBundleProduct extends Pivot
{
    use BroadcastsEvents;

    protected $table = 'product_bundle_product';

    public $timestamps = false;

    public $incrementing = true;

    public function bundleProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bundle_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
