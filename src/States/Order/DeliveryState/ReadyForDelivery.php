<?php

namespace FluxErp\States\Order\DeliveryState;

use FluxErp\Models\StateSetting;

class ReadyForDelivery extends DeliveryState
{
    public static $name = 'ready_for_delivery';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
