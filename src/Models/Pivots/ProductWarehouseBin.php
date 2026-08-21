<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Product;
use FluxErp\Models\WarehouseBin;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWarehouseBin extends FluxPivot
{
    protected $table = 'product_warehouse_bin';

    // Relations
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class);
    }
}
