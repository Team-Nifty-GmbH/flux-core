<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Client;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderPositionStockPosting extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'order_position_stock_posting';

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class, 'order_position_id');
    }

    public function stockPosting(): BelongsTo
    {
        return $this->belongsTo(StockPosting::class, 'stock_posting_id');
    }
}
