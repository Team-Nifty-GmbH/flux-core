<?php

namespace FluxErp\Enums;

use Carbon\CarbonImmutable;
use FluxErp\Enums\Traits\EnumTrait;

enum TimeFrameEnum: string
{
    use EnumTrait;

    case Today = 'Today';

    case Yesterday = 'Yesterday';

    case ThisWeek = 'This Week';

    case ThisMonth = 'This Month';

    case ThisQuarter = 'This Quarter';

    case ThisYear = 'This Year';

    case Custom = 'Custom';

    public function getRange(): ?array
    {
        $now = CarbonImmutable::now();

        return match ($this) {
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
            TimeFrameEnum::Custom => null,
        };
    }

    public function getPreviousRange(): ?array
    {
        $now = CarbonImmutable::now();

        return match ($this) {
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
            TimeFrameEnum::Custom => null,
        };
    }

    public function getUnit(): ?string
    {
        return match ($this) {
            TimeFrameEnum::Today, TimeFrameEnum::Yesterday, TimeFrameEnum::ThisMonth, TimeFrameEnum::ThisWeek => 'day',
            TimeFrameEnum::ThisQuarter, TimeFrameEnum::ThisYear => 'month',
            TimeFrameEnum::Custom => null,
        };
    }
}
