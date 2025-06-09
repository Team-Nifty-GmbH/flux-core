<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountDiscountGroup extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'discount_discount_group';

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class);
    }
}
