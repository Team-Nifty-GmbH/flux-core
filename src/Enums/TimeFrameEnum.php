<?php

namespace FluxErp\Enums;

use Carbon\CarbonImmutable;
use FluxErp\Enums\Traits\EnumTrait;

enum TimeFrameEnum: string
{
    use EnumTrait;

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

    public function getRange(): ?array
    {
        $now = CarbonImmutable::now();

        return match ($this) {
            TimeFrameEnum::Today => [
                $now->startOfDay(),
                $now->endOfDay(),
            ],
            TimeFrameEnum::Yesterday => [
                $now->subDay()->startOfDay(),
                $now->subDay()->endOfDay(),
            ],
            TimeFrameEnum::ThisWeek => [
                $now->startOfWeek()->startOfDay(),
                $now->endOfWeek()->endOfDay(),
            ],
            TimeFrameEnum::ThisMonth => [
                $now->startOfMonth()->startOfDay(),
                $now->endOfMonth()->endOfDay(),
            ],
            TimeFrameEnum::ThisQuarter => [
                $now->startOfQuarter()->startOfDay(),
                $now->endOfQuarter()->endOfDay(),
            ],
            TimeFrameEnum::ThisYear => [
                $now->startOfYear()->startOfDay(),
                $now->endOfYear()->endOfDay(),
            ],
            TimeFrameEnum::Custom => null,
        };
    }

    public function getUnit(): ?string
    {
        return match ($this) {
            TimeFrameEnum::Today, TimeFrameEnum::Yesterday, TimeFrameEnum::ThisMonth, TimeFrameEnum::ThisWeek => 'day',
            TimeFrameEnum::ThisQuarter => 'week',
            TimeFrameEnum::ThisYear => 'month',
            TimeFrameEnum::Custom => null,
        };
    }

    case Custom = 'Custom';

    case ThisMonth = 'This Month';

    case ThisQuarter = 'This Quarter';

    case ThisWeek = 'This Week';

    case ThisYear = 'This Year';

    case Today = 'Today';

    case Yesterday = 'Yesterday';
}
