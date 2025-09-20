<?php

namespace FluxErp\States\Order;

class Aborted extends OrderState
{
    public static bool $isEndState = true;

    public static $name = 'aborted';

    public function color(): string
    {
        return static::$color ?? 'red';
    }
}
