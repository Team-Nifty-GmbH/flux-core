<?php

namespace FluxErp\States\Order\DeliveryState;

use FluxErp\Models\StateSetting;

class Delivered extends DeliveryState
{
    public static $name = 'delivered';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}
