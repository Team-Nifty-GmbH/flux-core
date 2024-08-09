<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum PropertyTypeEnum: string
{
    use EnumTrait;

    case Text = 'text';
    case Option = 'option';
}
