<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum RoundingMethodEnum: string
{
    use EnumTrait;

    case None = 'none';

    case Round = 'round';

    case Ceil = 'ceil';

    case Floor = 'floor';

    case Nearest = 'nearest';

    case End = 'end';

    public static function valuesLocalized(): array
    {
        return array_combine(
            self::values(),
            [
                __('Do not round'),
                __('Round'),
                __('Round up'),
                __('Round down'),
                __('Round to nearest multiple'),
                __('Round to number'),
            ]
        );
    }
}
