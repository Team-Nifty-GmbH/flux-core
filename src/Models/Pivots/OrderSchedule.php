<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\Schedule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSchedule extends FluxPivot
{
    protected $table = 'order_schedule';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
