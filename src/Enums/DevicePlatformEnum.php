<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum DevicePlatformEnum: string
{
    use EnumTrait;

    case android = 'android';

    case ios = 'ios';

    case web = 'web';
}
