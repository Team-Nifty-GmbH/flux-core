<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountDiscountGroup extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'discount_discount_group';

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class, 'discount_group_id');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(DiscountDiscountGroup::class, 'discount_group_id', 'discount_group_id');
    }
}
