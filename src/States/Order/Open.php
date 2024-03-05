<?php

namespace FluxErp\States\Order;

class Open extends OrderState
{
    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'neutral';
    }
}
