<?php

namespace FluxErp\States\PaymentRun;

use FluxErp\Models\StateSetting;

class NotSuccessful extends PaymentRunState
{
    public static $name = 'not_successful';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'negative';
    }
}
