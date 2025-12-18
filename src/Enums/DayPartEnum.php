<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class DayPartEnum extends FluxEnum
{
    use EnumTrait;

    final public const string FullDay = 'full_day';

    final public const string FirstHalf = 'first_half';

    final public const string SecondHalf = 'second_half';
}
