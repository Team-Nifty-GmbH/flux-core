<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\Schedule;
use FluxErp\Traits\BroadcastsEvents;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSchedule extends FluxPivot
{
    use BroadcastsEvents;

    protected $table = 'order_schedule';

    public $timestamps = false;

    public $incrementing = true;

    protected $primaryKey = 'pivot_id';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
