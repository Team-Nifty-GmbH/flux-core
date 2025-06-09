<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum CreditAccountPostingEnum: int
{
    use EnumTrait;

    case Credit = 1;

    case Debit = -1;
}
