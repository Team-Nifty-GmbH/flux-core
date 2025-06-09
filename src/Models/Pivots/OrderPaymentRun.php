<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPaymentRun extends FluxPivot
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'order_payment_run';

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'success' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function paymentRun(): BelongsTo
    {
        return $this->belongsTo(PaymentRun::class, 'payment_run_id');
    }
}
