<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class TwoFactorMethodEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Totp = 'totp';

    final public const string Passkey = 'passkey';
}
