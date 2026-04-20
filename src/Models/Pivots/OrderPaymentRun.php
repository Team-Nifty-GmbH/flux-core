<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPaymentRun extends FluxPivot
{
    protected $table = 'order_payment_run';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentRun(): BelongsTo
    {
        return $this->belongsTo(PaymentRun::class);
    }
}
