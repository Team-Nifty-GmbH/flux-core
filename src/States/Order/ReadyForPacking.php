<?php

namespace FluxErp\States\Order;

use FluxErp\Models\StateSetting;

class ReadyForPacking extends OrderState
{
    public static $name = 'ready_for_packing';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'teal';
    }
}
