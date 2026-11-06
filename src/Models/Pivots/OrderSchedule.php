<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\Schedule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSchedule extends FluxPivot
{
    protected $table = 'order_schedule';

    protected static function booted(): void
    {
        static::deleting(function (OrderSchedule $pivot): void {
            // A cascaded delete loads the pivot without its key, so compare by order instead
            if (! static::query()
                ->where('schedule_id', $pivot->schedule_id)
                ->where('order_id', '!=', $pivot->order_id)
                ->exists()
            ) {
                $pivot->schedule()->delete();
            }
        });
    }

    // Relations
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
