<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum RepaymentTypeEnum: string
{
    use EnumTrait;

    case Annuity = 'annuity';

    case Linear = 'linear';
}
