<?php

namespace FluxErp\States\Address;

class Active extends AdvertisingState
{
    public static $name = 'active';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
