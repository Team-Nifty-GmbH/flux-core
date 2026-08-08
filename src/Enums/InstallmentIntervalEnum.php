<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum InstallmentIntervalEnum: string
{
    use EnumTrait;

    case Monthly = 'monthly';

    case Quarterly = 'quarterly';

    case SemiAnnual = 'semi_annual';

    case Annual = 'annual';

    /**
     * The months between two installments, the unit the schedule is spaced and
     * the annual interest rate is scaled by.
     */
    public function months(): int
    {
        return match ($this) {
            self::Monthly => 1,
            self::Quarterly => 3,
            self::SemiAnnual => 6,
            self::Annual => 12,
        };
    }
}
