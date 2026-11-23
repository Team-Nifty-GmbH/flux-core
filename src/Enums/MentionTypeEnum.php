<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class MentionTypeEnum extends FluxEnum
{
    use EnumTrait;

    final public const string User = 'user';

    final public const string Record = 'record';
}
