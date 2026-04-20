<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPositionTask extends FluxPivot
{
    protected $table = 'order_position_stock_posting';

    public function orderPosition(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
