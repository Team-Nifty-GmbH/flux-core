<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ComposerRepositoryTypeEnum: string
{
    use EnumTrait;

    case Composer = 'composer';

    case Package = 'package';

    case Path = 'path';

    case Vcs = 'vcs';
}
