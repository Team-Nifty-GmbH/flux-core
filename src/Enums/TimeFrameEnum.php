<?php

namespace FluxErp\Enums;

use Carbon\CarbonImmutable;
use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class TimeFrameEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Today = 'Today';

    final public const string Yesterday = 'Yesterday';

    final public const string ThisWeek = 'This Week';

    final public const string ThisMonth = 'This Month';

    final public const string ThisQuarter = 'This Quarter';

    final public const string ThisYear = 'This Year';

    final public const string LastWeek = 'Last Week';

    final public const string LastMonth = 'Last Month';

    final public const string LastQuarter = 'Last Quarter';

    final public const string LastYear = 'Last Year';

    final public const string Custom = 'Custom';

    public static function getPreviousRange(string $case): ?array
    {
        $now = CarbonImmutable::now();

        return match ($case) {
            TimeFrameEnum::Today => [
                $now->subDay()->startOfDay(),
                $now->subDay(),
            ],
            TimeFrameEnum::Yesterday => [
                $now->subDays(2)->startOfDay(),
                $now->subDays(2),
            ],
            TimeFrameEnum::ThisWeek => [
                $now->subWeek()->startOfWeek(),
                $now->subWeek(),
            ],
            TimeFrameEnum::ThisMonth => [
                $now->subMonthNoOverflow()->startOfMonth(),
                $now->subMonthNoOverflow(),
            ],
            TimeFrameEnum::ThisQuarter => [
                $now->subQuarter()->startOfQuarter(),
                $now->subQuarter(),
            ],
            TimeFrameEnum::ThisYear => [
                $now->subYear()->startOfYear(),
                $now->subYear(),
            ],
            TimeFrameEnum::LastWeek => [
                $now->subWeeks(2)->startOfWeek(),
                $now->subWeeks(2)->endOfWeek(),
            ],
            TimeFrameEnum::LastMonth => [
                $now->subMonthsNoOverflow(2)->startOfMonth(),
                $now->subMonthsNoOverflow(2)->endOfMonth(),
            ],
            TimeFrameEnum::LastQuarter => [
                $now->subQuarters(2)->startOfQuarter(),
                $now->subQuarters(2)->endOfQuarter(),
            ],
            TimeFrameEnum::LastYear => [
                $now->subYears(2)->startOfYear(),
                $now->subYears(2)->endOfYear(),
            ],
            default => null,
        };
    }

    public static function getRange(string $case): ?array
    {
        $now = CarbonImmutable::now();

        return match ($case) {
            TimeFrameEnum::Today => [
                $now->startOfDay(),
                $now,
            ],
            TimeFrameEnum::Yesterday => [
                $now->subDay()->startOfDay(),
                $now->subDay(),
            ],
            TimeFrameEnum::ThisWeek => [
                $now->startOfWeek(),
                $now,
            ],
            TimeFrameEnum::ThisMonth => [
                $now->startOfMonth(),
                $now,
            ],
            TimeFrameEnum::ThisQuarter => [
                $now->startOfQuarter(),
                $now,
            ],
            TimeFrameEnum::ThisYear => [
                $now->startOfYear(),
                $now,
            ],
            TimeFrameEnum::LastWeek => [
                $now->subWeek()->startOfWeek(),
                $now->subWeek()->endOfWeek(),
            ],
            TimeFrameEnum::LastMonth => [
                $now->subMonth()->startOfMonth(),
                $now->subMonth()->endOfMonth(),
            ],
            TimeFrameEnum::LastQuarter => [
                $now->subQuarter()->startOfQuarter(),
                $now->subQuarter()->endOfQuarter(),
            ],
            TimeFrameEnum::LastYear => [
                $now->subYear()->startOfYear(),
                $now->subYear()->endOfYear(),
            ],
            default => null,
        };
    }

    public static function getUnit(string $case): ?string
    {
        return match ($case) {
            TimeFrameEnum::Today,
            TimeFrameEnum::Yesterday,
            TimeFrameEnum::ThisMonth,
            TimeFrameEnum::LastMonth,
            TimeFrameEnum::LastWeek,
            TimeFrameEnum::ThisWeek => 'day',
            TimeFrameEnum::ThisQuarter, TimeFrameEnum::LastQuarter => 'week',
            TimeFrameEnum::ThisYear, TimeFrameEnum::LastYear => 'month',
            default => null,
        };
    }
}
