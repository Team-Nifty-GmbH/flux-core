<?php

namespace FluxErp\States\Order\PaymentState;

use FluxErp\Models\StateSetting;

class Open extends PaymentState
{
    public static $name = 'open';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'negative';
    }
}
