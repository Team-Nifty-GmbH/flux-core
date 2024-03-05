<?php

namespace FluxErp\States\PaymentRun;

class Pending extends PaymentRunState
{
    public static $name = 'pending';

    public function color(): string
    {
        return static::$color ?? 'warning';
    }
}
