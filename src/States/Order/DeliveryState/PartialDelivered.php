<?php

namespace FluxErp\States\Order\DeliveryState;

class PartialDelivered extends DeliveryState
{
    public static $name = 'partial_delivered';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
