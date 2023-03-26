<?php

namespace FluxErp\States\Order;

use FluxErp\Models\StateSetting;

class InProgress extends OrderState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'cyan';
    }
}
