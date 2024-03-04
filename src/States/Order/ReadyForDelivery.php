<?php

namespace FluxErp\States\Order;

class ReadyForDelivery extends OrderState
{
    public static $name = 'ready_for_delivery';

    public function color(): string
    {
        return static::$color ?? 'teal';
    }
}
