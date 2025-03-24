<?php

namespace FluxErp\States\Order\DeliveryState;

class Open extends DeliveryState
{
    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'red';
    }
}
