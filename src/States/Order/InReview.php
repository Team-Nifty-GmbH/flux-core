<?php

namespace FluxErp\States\Order;

class InReview extends OrderState
{
    public static $name = 'in_review';

    public function color(): string
    {
        return static::$color ?? 'teal';
    }
}
