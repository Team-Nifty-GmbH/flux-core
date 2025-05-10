<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPaymentRun extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = ['order_id', 'payment_run_id'];

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
