<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductProductOption extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'product_product_option';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(ProductProductOption::class, 'product_id', 'product_id');
    }
}
