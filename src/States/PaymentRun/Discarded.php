<?php

namespace FluxErp\States\PaymentRun;

use FluxErp\Models\StateSetting;

class Discarded extends PaymentRunState
{
    public static $name = 'discarded';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'neutral';
    }
}
