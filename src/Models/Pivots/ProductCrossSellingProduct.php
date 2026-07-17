<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCrossSellingProduct extends FluxPivot
{
    protected $table = 'product_cross_selling_product';

    // Relations
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productCrossSelling(): BelongsTo
    {
        return $this->belongsTo(ProductCrossSelling::class);
    }
}
