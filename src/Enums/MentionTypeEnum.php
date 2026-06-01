<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum MentionTypeEnum: string
{
    use EnumTrait;

    case User = 'user';

    case Record = 'record';

    case Channel = 'channel';

    case Here = 'here';
}
