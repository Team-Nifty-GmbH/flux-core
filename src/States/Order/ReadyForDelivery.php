<?php

namespace FluxErp\States\Order;

use FluxErp\Models\StateSetting;

class ReadyForDelivery extends OrderState
{
    public static $name = 'ready_for_delivery';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'teal';
    }
}
