<?php

namespace FluxErp\States\PaymentRun;

class Open extends PaymentRunState
{
    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'negative';
    }
}
