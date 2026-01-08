<?php

namespace FluxErp\Enums;

use Carbon\CarbonImmutable;
use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class ComparisonTypeEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Auto = 'Auto';

    final public const string PreviousMonth = 'Previous Month';

    final public const string PreviousQuarter = 'Previous Quarter';

    final public const string PreviousYear = 'Previous Year';

    final public const string Custom = 'Custom';

    public static function getComparisonRange(string $case, CarbonImmutable $start, CarbonImmutable $end): ?array
    {
        return match ($case) {
            self::PreviousMonth => [
                $start->subMonthNoOverflow()->startOfMonth(),
                $end->subMonthNoOverflow()->endOfMonth(),
            ],
            self::PreviousQuarter => [
                $start->subQuarter()->startOfQuarter(),
                $end->subQuarter()->endOfQuarter(),
            ],
            self::PreviousYear => [
                $start->subYear()->startOfYear(),
                $end->subYear()->endOfYear(),
            ],
            default => null,
        };
    }
}
