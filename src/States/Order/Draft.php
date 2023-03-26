<?php

namespace FluxErp\States\Order;

use FluxErp\Models\StateSetting;

class Draft extends OrderState
{
    public static $name = 'draft';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'teal';
    }
}
