<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\StockPosting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPositionStockPosting extends FluxPivot
{
    protected $table = 'order_position_stock_posting';

    // Relations
    /**
     * @return BelongsTo<OrderPosition, $this>
     */
    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    /**
     * @return BelongsTo<StockPosting, $this>
     */
    public function stockPosting(): BelongsTo
    {
        return $this->belongsTo(StockPosting::class);
    }
}
