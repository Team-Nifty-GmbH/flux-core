<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ConfigurationRuleTypeEnum: string
{
    use EnumTrait;

    case Requires = 'requires';

    case Excludes = 'excludes';
}
