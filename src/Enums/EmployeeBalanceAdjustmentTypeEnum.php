<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum EmployeeBalanceAdjustmentTypeEnum: string
{
    use EnumTrait;

    case Overtime = 'overtime';

    case Vacation = 'vacation';
}
