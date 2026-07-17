<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Address;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressProduct extends FluxPivot
{
    protected $table = 'address_product';

    // Relations
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
