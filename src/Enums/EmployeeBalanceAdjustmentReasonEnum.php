<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class EmployeeBalanceAdjustmentReasonEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Carryover = 'carryover';

    final public const string Compensation = 'compensation';

    final public const string Correction = 'correction';

    final public const string InitialBalance = 'initial_balance';

    final public const string Payout = 'payout';

    final public const string SpecialLeave = 'special_leave';

    final public const string Other = 'other';
}
