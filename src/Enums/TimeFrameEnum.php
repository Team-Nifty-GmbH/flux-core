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

    final public const string LastMonth = 'Last Month';

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
                $now->subMonthWithoutOverflow()->startOfMonth(),
                $now->subMonthWithoutOverflow(),
            ],
            TimeFrameEnum::ThisQuarter => [
                $now->subQuarter()->startOfQuarter(),
                $now->subQuarter(),
            ],
            TimeFrameEnum::ThisYear => [
                $now->subYear()->startOfYear(),
                $now->subYear(),
            ],
            TimeFrameEnum::LastMonth => [
                $now->subMonthsWithoutOverflow(2)->startOfMonth(),
                $now->subMonthsWithoutOverflow(2),
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
            TimeFrameEnum::LastMonth => [
                $now->subMonth()->startOfMonth(),
                $now->subMonth()->endOfMonth(),
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
            TimeFrameEnum::ThisWeek => 'day',
            TimeFrameEnum::ThisQuarter => 'week',
            TimeFrameEnum::ThisYear => 'month',
            default => null,
        };
    }
}
