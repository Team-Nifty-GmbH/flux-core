<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ComposerRepositoryTypeEnum: string
{
    use EnumTrait;

    case Composer = 'composer';

    case Vcs = 'vcs';

    case Package = 'package';

    case Path = 'path';
}
