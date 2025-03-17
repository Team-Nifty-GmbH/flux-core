<?php

namespace FluxErp\States\Order\PaymentState;

class Open extends PaymentState
{
    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'red';
    }
}
