<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ComposerRepositoryTypeEnum: string
{
    use EnumTrait;

    case composer = 'composer';

    case vcs = 'vcs';

    case package = 'package';

    case path = 'path';
}
