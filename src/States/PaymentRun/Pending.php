<?php

namespace FluxErp\States\PaymentRun;

use FluxErp\Models\StateSetting;

class Pending extends PaymentRunState
{
    public static $name = 'pending';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
