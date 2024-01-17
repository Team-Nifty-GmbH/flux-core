<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum TimeUnitEnum: string
{
    use EnumTrait;

    case Second = 'Second';

    case Minute = 'Minute';

    case Hour = 'Hour';

    case Day = 'Day';

    case Week = 'Week';

    case Month = 'Month';

    case Year = 'Year';

    public function convertFromMiliseconds($miliseconds): float|int
    {
        return $this->convertFromSeconds($miliseconds / 1000);
    }

    public function convertFromSeconds($seconds): float|int
    {
        return match ($this) {
            self::Second => $seconds,
            self::Minute => $seconds / 60,
            self::Hour => $seconds / 3600,
            self::Day => $seconds / 86400,
            self::Week => $seconds / 604800,
            self::Month => $seconds / 2628000,
            self::Year => $seconds / 31536000,
        };
    }

    public function convertFromMinutes($minutes): float|int
    {
        return match ($this) {
            self::Second => $minutes * 60,
            self::Minute => $minutes,
            self::Hour => $minutes / 60,
            self::Day => $minutes / 1440,
            self::Week => $minutes / 10080,
            self::Month => $minutes / 43800,
            self::Year => $minutes / 525600,
        };
    }

    public function convertFromHours($hours): float|int
    {
        return match ($this) {
            self::Second => $hours * 3600,
            self::Minute => $hours * 60,
            self::Hour => $hours,
            self::Day => $hours / 24,
            self::Week => $hours / 168,
            self::Month => $hours / 730,
            self::Year => $hours / 8760,
        };
    }

    public function convertFromDays($days): float|int
    {
        return match ($this) {
            self::Second => $days * 86400,
            self::Minute => $days * 1440,
            self::Hour => $days * 24,
            self::Day => $days,
            self::Week => $days / 7,
            self::Month => $days / 30,
            self::Year => $days / 365,
        };
    }

    public function convertFromMonths($months): float|int
    {
        return match ($this) {
            self::Second => $months * 2628000,
            self::Minute => $months * 43800,
            self::Hour => $months * 730,
            self::Day => $months * 30,
            self::Week => $months * 4,
            self::Month => $months,
            self::Year => $months / 12,
        };
    }

    public function convertFromYears($years): float|int
    {
        return match ($this) {
            self::Second => $years * 31536000,
            self::Minute => $years * 525600,
            self::Hour => $years * 8760,
            self::Day => $years * 365,
            self::Week => $years * 52,
            self::Month => $years * 12,
            self::Year => $years,
        };
    }
}
