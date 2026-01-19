<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum SubscriptionCancellationTypeEnum: string
{
    use EnumTrait;

    case Immediate = 'immediate';

    case NextPeriod = 'next_period';
}
