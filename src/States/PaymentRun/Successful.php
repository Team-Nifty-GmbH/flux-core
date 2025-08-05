<?php

namespace FluxErp\States\PaymentRun;

class Successful extends PaymentRunState
{
    public static $name = 'successful';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
