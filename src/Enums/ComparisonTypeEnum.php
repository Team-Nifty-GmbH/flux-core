<?php

namespace FluxErp\Enums;

use Carbon\CarbonInterface;
use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class ComparisonTypeEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Auto = 'Auto';

    final public const string PreviousDay = 'Previous Day';

    final public const string PreviousWeek = 'Previous Week';

    final public const string PreviousMonth = 'Previous Month';

    final public const string PreviousQuarter = 'Previous Quarter';

    final public const string PreviousYear = 'Previous Year';

    final public const string Custom = 'Custom';

    public static function getComparisonRange(string $case, CarbonInterface $start, CarbonInterface $end): ?array
    {
        return match ($case) {
            self::PreviousDay => [
                $start->subDay()->startOfDay(),
                $end->subDay()->endOfDay(),
            ],
            self::PreviousWeek => [
                $start->subWeek()->startOfWeek(),
                $end->subWeek()->endOfWeek(),
            ],
            self::PreviousMonth => [
                $start->subMonthNoOverflow()->startOfMonth(),
                $end->subMonthNoOverflow()->endOfMonth(),
            ],
            self::PreviousQuarter => [
                $start->subQuarterNoOverflow()->startOfQuarter(),
                $end->subQuarterNoOverflow()->endOfQuarter(),
            ],
            self::PreviousYear => [
                $start->subYear()->startOfYear(),
                $end->subYear()->endOfYear(),
            ],
            default => null,
        };
    }
}
