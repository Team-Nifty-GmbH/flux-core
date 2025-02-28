<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ToastType: string
{
    use EnumTrait;

    case SUCCESS = 'success';

    case ERROR = 'error';

    case WARNING = 'warning';

    case INFO = 'info';

    case QUESTION = 'question';
}
