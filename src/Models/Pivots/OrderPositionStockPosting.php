<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\StockPosting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPositionStockPosting extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'order_position_stock_posting';

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function stockPosting(): BelongsTo
    {
        return $this->belongsTo(StockPosting::class);
    }
}
