<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class ProductProductOption extends Pivot
{
    use BroadcastsEvents;

    protected $table = 'product_product_option';

    public $timestamps = false;

    public $incrementing = true;

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
