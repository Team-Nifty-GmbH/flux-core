<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum RepeatableTypeEnum: string
{
    use EnumTrait;

    case Command = 'command';
    case Invokable = 'invokable';
    case Job = 'job';
    case Shell = 'shell';
}
