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
            if (! static::query()->where('schedule_id', $pivot->schedule_id)->whereKeyNot($pivot)->exists()) {
                $pivot->schedule()->delete();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
