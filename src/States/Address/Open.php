<?php

namespace FluxErp\States\Address;

class Open extends AdvertisingState
{
    public static $name = 'open';

    public function color(): string
    {
        return static::$color ?? 'negative';
    }
}
