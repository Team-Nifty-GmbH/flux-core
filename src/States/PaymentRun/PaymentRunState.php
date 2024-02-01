<?php

namespace FluxErp\States\PaymentRun;

use FluxErp\States\State;
use TeamNiftyGmbH\DataTable\Contracts\HasFrontendFormatter;

abstract class PaymentRunState extends State implements HasFrontendFormatter
{
    public static function getFrontendFormatter(...$args): string|array
    {
        return [
            'state',
            self::getStateMapping()
                ->map(fn ($key) => (new $key(''))->color()),
        ];
    }
}
