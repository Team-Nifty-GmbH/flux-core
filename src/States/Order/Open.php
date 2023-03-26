<?php

namespace FluxErp\States\Order;

use FluxErp\Models\StateSetting;

class Open extends OrderState
{
    public static $name = 'open';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'neutral';
    }
}
