<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum DepreciationMethodEnum: string
{
    use EnumTrait;

    case Linear = 'linear';
}
