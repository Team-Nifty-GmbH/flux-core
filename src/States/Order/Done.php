<?php

namespace FluxErp\States\Order;

class Done extends OrderState
{
    public static bool $isEndState = true;

    public static $name = 'done';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
