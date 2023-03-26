<?php

namespace FluxErp\States\Order\PaymentState;

use FluxErp\Models\StateSetting;

class PartialPaid extends PaymentState
{
    public static $name = 'partial_paid';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
