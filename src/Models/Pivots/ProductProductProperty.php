<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use FluxErp\Models\ProductProperty;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProductProperty extends FluxPivot
{
    protected $table = 'product_product_property';

    // Relations
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<ProductProperty, $this>
     */
    public function productProperty(): BelongsTo
    {
        return $this->belongsTo(ProductProperty::class);
    }
}
