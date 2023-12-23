<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum RepeatableTypeEnum: string
{
    use EnumTrait;

    case Command = 'command';
    case Job = 'job';
    case Invokable = 'invokable';
    case Shell = 'shell';
}
