<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum LedgerAccountTypeEnum: string
{
    use EnumTrait;

    case Revenue = 'revenue';

    case Expense = 'expense';

    case Asset = 'asset';

    case Liability = 'liability';
}
