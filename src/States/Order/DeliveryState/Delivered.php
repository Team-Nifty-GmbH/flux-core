<?php

namespace FluxErp\States\Order\DeliveryState;

class Delivered extends DeliveryState
{
    public static $name = 'delivered';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
