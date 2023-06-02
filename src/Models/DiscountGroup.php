<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DiscountGroup extends Model
{
    use HasUserModification;

    protected $guarded = [
        'id',
    ];

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_discount_group');
    }
}
