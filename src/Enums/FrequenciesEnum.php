<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;

enum FrequenciesEnum: string
{
    use EnumTrait;

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
    case At = 'at';
    case Between = 'between';
    case Daily = 'daily';
    case DailyAt = 'dailyAt';
    case Days = 'days';
    case EveryFifteenMinutes = 'everyFifteenMinutes';
    case EveryFifteenSeconds = 'everyFifteenSeconds';
    case EveryFiveMinutes = 'everyFiveMinutes';
    case EveryFiveSeconds = 'everyFiveSeconds';
    case EveryFourHours = 'everyFourHours';
    case EveryFourMinutes = 'everyFourMinutes';
    case EveryMinute = 'everyMinute';
    case EveryOddHour = 'everyOddHour';

    case EverySecond = 'everySecond';
    case EverySixHours = 'everySixHours';
    case EveryTenMinutes = 'everyTenMinutes';
    case EveryTenSeconds = 'everyTenSeconds';
    case EveryThirtyMinutes = 'everyThirtyMinutes';
    case EveryThirtySeconds = 'everyThirtySeconds';
    case EveryThreeHours = 'everyThreeHours';
    case EveryThreeMinutes = 'everyThreeMinutes';
    case EveryTwentySeconds = 'everyTwentySeconds';
    case EveryTwoHours = 'everyTwoHours';
    case EveryTwoMinutes = 'everyTwoMinutes';
    case EveryTwoSeconds = 'everyTwoSeconds';
    case Fridays = 'fridays';
    case Hourly = 'hourly';
    case HourlyAt = 'hourlyAt';
    case LastDayOfMonth = 'lastDayOfMonth';
    case Mondays = 'mondays';
    case Monthly = 'monthly';
    case MonthlyOn = 'monthlyOn';
    case Quarterly = 'quarterly';
    case QuarterlyOn = 'quarterlyOn';
    case Saturdays = 'saturdays';
    case Sundays = 'sundays';
    case Thursdays = 'thursdays';
    case Tuesdays = 'tuesdays';
    case TwiceDaily = 'twiceDaily';
    case TwiceDailyAt = 'twiceDailyAt';
    case TwiceMonthly = 'twiceMonthly';
    case UnlessBetween = 'unlessBetween';
    case Wednesdays = 'wednesdays';
    case Weekdays = 'weekdays';
    case Weekends = 'weekends';
    case Weekly = 'weekly';
    case WeeklyOn = 'weeklyOn';
    case Yearly = 'yearly';
    case YearlyOn = 'yearlyOn';
}
