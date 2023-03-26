<?php

namespace FluxErp\States\Order;

use FluxErp\Models\StateSetting;

class InReview extends OrderState
{
    public static $name = 'in_review';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'teal';
    }
}
