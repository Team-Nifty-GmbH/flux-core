<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum SalaryTypeEnum: string
{
    use EnumTrait;

    case Hourly = 'hourly';

    case Monthly = 'monthly';

    case Annual = 'annual';
}
