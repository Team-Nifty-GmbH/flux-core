<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum SepaMandateTypeEnum: string
{
    use EnumTrait;

    case B2B = 'B2B';

    case BASIC = 'BASIC';
}
