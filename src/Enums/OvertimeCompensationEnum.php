<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum OvertimeCompensationEnum: string
{
    use EnumTrait;

    case Mixed = 'mixed';

    case None = 'none';

    case Payment = 'payment';

    case TimeOff = 'time_off';
}
