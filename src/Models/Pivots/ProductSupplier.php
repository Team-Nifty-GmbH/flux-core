<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSupplier extends FluxPivot
{
    protected $table = 'product_supplier';

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
