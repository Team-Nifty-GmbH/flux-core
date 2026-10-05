<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ScheduleAdjustmentTypeEnum: string
{
    use EnumTrait;

    case ShortenTerm = 'shorten_term';

    case ReduceInstallment = 'reduce_installment';
}
