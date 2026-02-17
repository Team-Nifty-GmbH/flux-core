<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class ContactOptionTypeEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Phone = 'phone';

    final public const string Email = 'email';

    final public const string Website = 'website';
}
