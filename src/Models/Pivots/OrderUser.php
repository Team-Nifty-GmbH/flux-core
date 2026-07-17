<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderUser extends FluxPivot
{
    protected $table = 'order_user';

    // Relations
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
