<?php

namespace FluxErp\States\Order\DeliveryState;

use FluxErp\Models\StateSetting;

class PartialDelivered extends DeliveryState
{
    public static $name = 'partial_delivered';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
