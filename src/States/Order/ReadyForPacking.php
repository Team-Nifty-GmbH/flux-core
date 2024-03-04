<?php

namespace FluxErp\States\Order;

class ReadyForPacking extends OrderState
{
    public static $name = 'ready_for_packing';

    public function color(): string
    {
        return static::$color ?? 'teal';
    }
}
