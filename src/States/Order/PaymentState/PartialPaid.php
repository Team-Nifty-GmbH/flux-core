<?php

namespace FluxErp\States\Order\PaymentState;

class PartialPaid extends PaymentState
{
    public static $name = 'partial_paid';

    public function color(): string
    {
        return static::$color ?? 'warning';
    }
}
