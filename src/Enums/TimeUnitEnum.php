<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum TimeUnitEnum: string
{
    use EnumTrait;

    public function convertFromDays($days): string|float|int
    {
        return match ($this) {
            self::Second => bcmul($days, 86400),
            self::Minute => bcmul($days, 1440),
            self::Hour => bcmul($days, 24),
            self::Day => $days,
            self::Week => bcdiv($days, 7),
            self::Month => bcdiv($days, 30),
            self::Year => bcdiv($days, 365),
        };
    }

    public function convertFromHours($hours): string|float|int
    {
        return match ($this) {
            self::Second => bcmul($hours, 3600),
            self::Minute => bcmul($hours, 60),
            self::Hour => $hours,
            self::Day => bcdiv($hours, 24),
            self::Week => bcdiv($hours, 168),
            self::Month => bcdiv($hours, 720),
            self::Year => bcdiv($hours, 8760),
        };
    }

    public function convertFromMilliseconds($milliseconds): string
    {
        return $this->convertFromSeconds(bcdiv($milliseconds, 1000));
    }

    public function convertFromMinutes($minutes): string|float|int
    {
        return match ($this) {
            self::Second => bcmul($minutes, 60),
            self::Minute => $minutes,
            self::Hour => bcdiv($minutes, 60),
            self::Day => bcdiv($minutes, 1440),
            self::Week => bcdiv($minutes, 10080),
            self::Month => bcdiv($minutes, 43200),
            self::Year => bcdiv($minutes, 525600),
        };
    }

    public function convertFromMonths($months): string|float|int
    {
        return match ($this) {
            self::Second => bcmul($months, 2592000),
            self::Minute => bcmul($months, 43200),
            self::Hour => bcmul($months, 720),
            self::Day => bcmul($months, 30),
            self::Week => bcmul($months, 4),
            self::Month => $months,
            self::Year => bcdiv($months, 12),
        };
    }

    public function convertFromSeconds($seconds): string|float|int
    {
        return match ($this) {
            self::Second => $seconds,
            self::Minute => bcdiv($seconds, 60),
            self::Hour => bcdiv($seconds, 3600),
            self::Day => bcdiv($seconds, 86400),
            self::Week => bcdiv($seconds, 604800),
            self::Month => bcdiv($seconds, 2592000),
            self::Year => bcdiv($seconds, 31536000),
        };
    }

    public function convertFromYears($years): string|float|int
    {
        return match ($this) {
            self::Second => bcmul($years, 31536000),
            self::Minute => bcmul($years, 525600),
            self::Hour => bcmul($years, 8760),
            self::Day => bcmul($years, 365),
            self::Week => bcmul($years, 52),
            self::Month => bcmul($years, 12),
            self::Year => $years,
        };
    }

    case Day = 'Day';

    case Hour = 'Hour';

    case Minute = 'Minute';

    case Month = 'Month';

    case Second = 'Second';

    case Week = 'Week';

    case Year = 'Year';
}
