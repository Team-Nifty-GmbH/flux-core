<?php

namespace FluxErp\States\Order;

use FluxErp\Models\StateSetting;

class Canceled extends OrderState
{
    public static $name = 'canceled';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'negative';
    }
}
