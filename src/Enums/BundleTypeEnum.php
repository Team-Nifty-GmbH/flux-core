<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum BundleTypeEnum: string
{
    use EnumTrait;

    case Group = 'group';

    case Standard = 'standard';
}
