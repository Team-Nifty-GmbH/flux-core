<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum PaymentIntervalEnum: string
{
    use EnumTrait;

    case Weekly = 'weekly';

    case Biweekly = 'biweekly';

    case Monthly = 'monthly';

    case Quarterly = 'quarterly';

    case Annual = 'annual';

    public function paymentsPerYear(): int
    {
        return match ($this) {
            self::Weekly => 52,
            self::Biweekly => 26,
            self::Monthly => 12,
            self::Quarterly => 4,
            self::Annual => 1,
        };
    }
}
