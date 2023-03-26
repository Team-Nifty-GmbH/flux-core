<?php

namespace FluxErp\States\Order\DeliveryState;

use FluxErp\Models\StateSetting;

class InProgress extends DeliveryState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
