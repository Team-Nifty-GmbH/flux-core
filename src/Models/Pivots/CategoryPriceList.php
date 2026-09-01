<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Category;
use FluxErp\Models\Discount;
use FluxErp\Models\PriceList;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryPriceList extends FluxPivot
{
    protected $table = 'category_price_list';

    // Relations
    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Discount, $this>
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * @return BelongsTo<PriceList, $this>
     */
    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }
}
