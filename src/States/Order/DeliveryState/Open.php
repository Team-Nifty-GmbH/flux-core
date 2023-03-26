<?php

namespace FluxErp\States\Order\DeliveryState;

use FluxErp\Models\StateSetting;

class Open extends DeliveryState
{
    public static $name = 'open';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'negative';
    }
}
