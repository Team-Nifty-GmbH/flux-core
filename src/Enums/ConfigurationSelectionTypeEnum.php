<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ConfigurationSelectionTypeEnum: string
{
    use EnumTrait;

    case Single = 'single';

    case Multiple = 'multiple';
}
