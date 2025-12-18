<?php

namespace FluxErp\States\Order\PaymentState;

class Paid extends PaymentState
{
    public static bool $isEndState = true;

    public static $name = 'paid';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
