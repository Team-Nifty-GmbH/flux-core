<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Contact;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSupplier extends FluxPivot
{
    protected static array $pivotColumns = [
        'manufacturer_product_number',
        'supplier_product_number',
        'supplier_product_name',
        'packaging_amount',
        'packaging_unit_id',
        'purchase_price',
        'note',
    ];

    protected $table = 'product_supplier';

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function packagingUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
