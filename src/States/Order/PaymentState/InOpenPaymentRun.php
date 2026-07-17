<?php

namespace FluxErp\States\Order\PaymentState;

class InOpenPaymentRun extends PaymentState
{
    public static $name = 'in_open_payment_run';

    public function color(): string
    {
        return static::$color ?? 'sky';
    }
}
