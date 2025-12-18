<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum FrequenciesEnum: string
{
    use EnumTrait;

    case EverySecond = 'everySecond';
    case EveryTwoSeconds = 'everyTwoSeconds';
    case EveryFiveSeconds = 'everyFiveSeconds';
    case EveryTenSeconds = 'everyTenSeconds';
    case EveryFifteenSeconds = 'everyFifteenSeconds';
    case EveryTwentySeconds = 'everyTwentySeconds';
    case EveryThirtySeconds = 'everyThirtySeconds';
    case EveryMinute = 'everyMinute';
    case EveryTwoMinutes = 'everyTwoMinutes';
    case EveryThreeMinutes = 'everyThreeMinutes';
    case EveryFourMinutes = 'everyFourMinutes';
    case EveryFiveMinutes = 'everyFiveMinutes';
    case EveryTenMinutes = 'everyTenMinutes';
    case EveryFifteenMinutes = 'everyFifteenMinutes';
    case EveryThirtyMinutes = 'everyThirtyMinutes';
    case Hourly = 'hourly';
    case HourlyAt = 'hourlyAt';
    case EveryOddHour = 'everyOddHour';
    case EveryTwoHours = 'everyTwoHours';
    case EveryThreeHours = 'everyThreeHours';
    case EveryFourHours = 'everyFourHours';
    case EverySixHours = 'everySixHours';
    case Daily = 'daily';
    case DailyAt = 'dailyAt';
    case TwiceDaily = 'twiceDaily';
    case TwiceDailyAt = 'twiceDailyAt';
    case Weekly = 'weekly';
    case WeeklyOn = 'weeklyOn';
    case Monthly = 'monthly';
    case MonthlyOn = 'monthlyOn';
    case TwiceMonthly = 'twiceMonthly';
    case LastDayOfMonth = 'lastDayOfMonth';
    case Quarterly = 'quarterly';
    case QuarterlyOn = 'quarterlyOn';
    case Yearly = 'yearly';
    case YearlyOn = 'yearlyOn';
    case Weekdays = 'weekdays';
    case Weekends = 'weekends';
    case Mondays = 'mondays';
    case Tuesdays = 'tuesdays';
    case Wednesdays = 'wednesdays';
    case Thursdays = 'thursdays';
    case Fridays = 'fridays';
    case Saturdays = 'saturdays';
    case Sundays = 'sundays';
    case Days = 'days';
    case At = 'at';
    case Between = 'between';
    case UnlessBetween = 'unlessBetween';

    public static function getBasicFrequencies(): array
    {
        return array_diff(
            self::values(),
            [
                'weekdays',
                'weekends',
                'mondays',
                'tuesdays',
                'wednesdays',
                'thursdays',
                'fridays',
                'saturdays',
                'sundays',
                'days',
                'at',
                'between',
                'unlessBetween',
            ]
        );
    }

    public static function getDayConstraints(): array
    {
        return array_intersect(
            self::values(),
            [
                'weekdays',
                'weekends',
                'mondays',
                'tuesdays',
                'wednesdays',
                'thursdays',
                'fridays',
                'saturdays',
                'sundays',
                'days',
            ]
        );
    }

    public static function getTimeConstraints(): array
    {
        return array_intersect(
            self::values(),
            [
                'at',
                'between',
                'unlessBetween',
            ]
        );
    }
}
