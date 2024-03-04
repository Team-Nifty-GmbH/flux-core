<?php

namespace FluxErp\States\Order\PaymentState;

class Paid extends PaymentState
{
    public static $name = 'paid';

    public function color(): string
    {
        return static::$color ?? 'positive';
    }
}
