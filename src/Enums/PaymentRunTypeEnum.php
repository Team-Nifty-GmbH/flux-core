<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum PaymentRunTypeEnum: string
{
    use EnumTrait;

    case MoneyTransfer = 'money_transfer';
    case DirectDebit = 'direct_debit';
}
