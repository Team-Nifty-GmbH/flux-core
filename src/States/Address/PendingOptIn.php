<?php

namespace FluxErp\States\Address;

class PendingOptIn extends AdvertisingState
{
    public static $name = 'pending_opt_in';

    public function color(): string
    {
        return static::$color ?? 'warning';
    }
}
