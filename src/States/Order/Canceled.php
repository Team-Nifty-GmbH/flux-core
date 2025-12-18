<?php

namespace FluxErp\States\Order;

class Canceled extends OrderState
{
    public static bool $isEndState = true;

    public static $name = 'canceled';

    public function color(): string
    {
        return static::$color ?? 'red';
    }
}
