<?php

namespace FluxErp\States\Order\PaymentState;

use FluxErp\Models\StateSetting;

class Paid extends PaymentState
{
    public static $name = 'paid';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}
