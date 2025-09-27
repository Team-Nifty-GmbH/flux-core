<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum DayPartEnum: string
{
    use EnumTrait;

    case FirstHalf = 'first_half';

    case FullDay = 'full_day';

    case SecondHalf = 'second_half';
}
