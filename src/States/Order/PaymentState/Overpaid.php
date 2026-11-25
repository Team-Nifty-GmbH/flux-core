<?php

namespace FluxErp\States\Order\PaymentState;

class Overpaid extends PaymentState
{
    public static $name = 'overpaid';

    public function color(): string
    {
        return static::$color ?? 'violet';
    }
}
