<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum PaymentRunTypeEnum: string
{
    use EnumTrait;
    case DirectDebit = 'direct_debit';

    case MoneyTransfer = 'money_transfer';

    public function expectedSign(): int
    {
        return match ($this) {
            self::DirectDebit => 1,
            self::MoneyTransfer => -1,
        };
    }
}
