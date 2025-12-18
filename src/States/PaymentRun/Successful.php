<?php

namespace FluxErp\States\PaymentRun;

class Successful extends PaymentRunState
{
    public static bool $isEndState = true;

    public static $name = 'successful';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
