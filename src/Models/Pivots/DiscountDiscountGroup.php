<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountDiscountGroup extends FluxPivot
{
    protected $table = 'discount_discount_group';

    // Relations
    /**
     * @return BelongsTo<Discount, $this>
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * @return BelongsTo<DiscountGroup, $this>
     */
    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class);
    }
}
