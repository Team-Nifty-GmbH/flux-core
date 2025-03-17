<?php

namespace FluxErp\States\Order;

class Canceled extends OrderState
{
    public static $name = 'canceled';

    public function color(): string
    {
        return static::$color ?? 'red';
    }
}
