<?php

namespace FluxErp\States\Order\DeliveryState;

class ReadyForDelivery extends DeliveryState
{
    public static $name = 'ready_for_delivery';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
