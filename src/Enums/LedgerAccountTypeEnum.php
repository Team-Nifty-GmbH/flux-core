<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum LedgerAccountTypeEnum: string
{
    use EnumTrait;

    case Asset = 'asset';

    case Expense = 'expense';

    case Liability = 'liability';

    case Revenue = 'revenue';
}
