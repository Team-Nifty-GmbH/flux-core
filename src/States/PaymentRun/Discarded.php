<?php

namespace FluxErp\States\PaymentRun;

class Discarded extends PaymentRunState
{
    public static $name = 'discarded';

    public function color(): string
    {
        return static::$color ?? 'neutral';
    }
}
