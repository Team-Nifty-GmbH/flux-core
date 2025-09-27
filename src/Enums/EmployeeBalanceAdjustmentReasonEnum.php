<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum EmployeeBalanceAdjustmentReasonEnum: string
{
    use EnumTrait;

    case Carryover = 'carryover';

    case Compensation = 'compensation';

    case Correction = 'correction';

    case InitialBalance = 'initial_balance';

    case Other = 'other';

    case Payout = 'payout';

    case SpecialLeave = 'special_leave';
}
