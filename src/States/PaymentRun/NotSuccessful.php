<?php

namespace FluxErp\States\PaymentRun;

class NotSuccessful extends PaymentRunState
{
    public static $name = 'not_successful';

    public function color(): string
    {
        return static::$color ?? 'negative';
    }
}
