<?php

namespace FluxErp\States\PaymentRun;

use FluxErp\Models\StateSetting;

class Open extends PaymentRunState
{
    public static $name = 'open';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'negative';
    }
}
