<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Category;
use FluxErp\Models\Discount;
use FluxErp\Models\PriceList;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryPriceList extends FluxPivot
{
    protected $table = 'category_price_list';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }
}
