<?php

namespace FluxErp\States\Order\DeliveryState;

class InProgress extends DeliveryState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
