<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum ToastType: string
{
    use EnumTrait;

    case ERROR = 'error';

    case INFO = 'info';

    case QUESTION = 'question';

    case SUCCESS = 'success';

    case WARNING = 'warning';
}
