<?php

namespace FluxErp\States\PaymentRun;

use FluxErp\Models\StateSetting;

class Successful extends PaymentRunState
{
    public static $name = 'successful';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}
