<?php

namespace FluxErp\States\Order;

class Draft extends OrderState
{
    public static $name = 'draft';

    public function color(): string
    {
        return static::$color ?? 'teal';
    }
}
