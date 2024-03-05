<?php

namespace FluxErp\States\Order;

class InProgress extends OrderState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return static::$color ?? 'cyan';
    }
}
