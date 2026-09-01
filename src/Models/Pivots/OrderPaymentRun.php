<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Order;
use FluxErp\Models\PaymentRun;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use FluxErp\States\Order\PaymentState\InPayment;
use FluxErp\States\Order\PaymentState\Open;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPaymentRun extends FluxPivot
{
    protected $table = 'order_payment_run';

    protected static function booted(): void
    {
        static::saved(function (OrderPaymentRun $orderPaymentRun): void {
            $orderPaymentRun->paymentRun?->recalculateTotalAmount();
        });

        static::deleted(function (OrderPaymentRun $orderPaymentRun): void {
            $orderPaymentRun->paymentRun?->recalculateTotalAmount();

            $order = $orderPaymentRun->order;

            if (! $order
                || (
                    ! $order->payment_state instanceof InOpenPaymentRun
                    && ! $order->payment_state instanceof InPayment
                )
            ) {
                return;
            }

            if ($order->paymentRuns()->exists()) {
                return;
            }

            if ($order->payment_state->canTransitionTo(Open::class)) {
                $order->payment_state->transitionTo(Open::class);
            }

            $order->calculatePaymentState()->save();
        });
    }

    // Relations
    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<PaymentRun, $this>
     */
    public function paymentRun(): BelongsTo
    {
        return $this->belongsTo(PaymentRun::class);
    }
}
