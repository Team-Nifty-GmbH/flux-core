<?php

namespace FluxErp\States\Order\PaymentState;

class InPayment extends PaymentState
{
    public static $name = 'in_payment';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
