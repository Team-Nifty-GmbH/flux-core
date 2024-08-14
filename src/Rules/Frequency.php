<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class Frequency implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    protected array $frequencies = [
        'everySecond' => null,
        'everyTwoSeconds' => null,
        'everyFiveSeconds' => null,
        'everyTenSeconds' => null,
        'everyFifteenSeconds' => null,
        'everyTwentySeconds' => null,
        'everyThirtySeconds' => null,
        'everyMinute' => null,
        'everyTwoMinutes' => null,
        'everyThreeMinutes' => null,
        'everyFourMinutes' => null,
        'everyFiveMinutes' => null,
        'everyTenMinutes' => null,
        'everyFifteenMinutes' => null,
        'everyThirtyMinutes' => null,
        'hourly' => null,
        'hourlyAt' => 'minute',
        'everyOddHour' => 'minute|nullable',
        'everyTwoHours' => 'minute|nullable',
        'everyThreeHours' => 'minute|nullable',
        'everyFourHours' => 'minute|nullable',
        'everySixHours' => 'minute|nullable',
        'daily' => null,
        'dailyAt' => 'time',
        'twiceDaily' => 'array:hour,hour',
        'twiceDailyAt' => 'array:hour,hour,minute',
        'weekly' => null,
        'weeklyOn' => 'array:weekday,time',
        'monthly' => null,
        'monthlyOn' => 'array:day,time',
        'twiceMonthly' => 'array:day,day,time',
        'lastDayOfMonth' => 'time',
        'quarterly' => null,
        'quarterlyOn' => 'array:day,time',
        'yearly' => null,
        'yearlyOn' => 'array:month,day,time',
        'weekdays' => null,
        'weekends' => null,
        'mondays' => null,
        'tuesdays' => null,
        'wednesdays' => null,
        'thursdays' => null,
        'fridays' => null,
        'saturdays' => null,
        'sundays' => null,
        'days' => 'array:weekdays',
        'at' => 'time',
        'between' => 'array:time,time',
        'unlessBetween' => 'array:time,time',
    ];

    public function __construct(protected string $frequencyKey) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $frequency = data_get($this->data, $this->frequencyKey);

        $validation = $this->frequencies[$frequency] ?? null;

        if (! is_array($value) || ! array_is_list($value)) {
            $fail('The :attribute must be an array list.')->translate();
        }

        if (is_null($validation) && $value !== []) {
            $fail('The :attribute must be an empty array.')->translate();
        }

        switch ($validation) {
            case 'minute':
                if (count($value) !== 1 || ! is_int($value[0]) || $value[0] > 59 || $value[0] < 0) {
                    $fail('The :attribute must be an array with one entry '.
                        'which must be a minute between 0 and 59.'
                    )
                        ->translate();
                }
                break;
            case 'minute|nullable':
                if (count($value) > 1
                    || count($value) === 1
                    && ! (is_null($value[0]) || (is_int($value[0]) && $value[0] < 60 && $value[0] > -1))
                ) {
                    $fail('The :attribute must be an array with max one entry, '.
                        'this entry must be null or a minute between 0 and 59.'
                    )->translate();
                }
                break;
            case 'time':
                if (count($value) !== 1
                    || ! is_string($value[0])
                    || ! preg_match('/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value[0])
                ) {
                    $fail('The :attribute must be an array with one entry, '.
                        'this entry must be a valid time string.'
                    )->translate();
                }
                break;
            case 'array:hour,hour':
                if (count($value) !== 2
                    || ! is_int($value[0])
                    || ! is_int($value[1])
                    || $value[0] > 23 || $value[0] < 0
                    || $value[1] > 23 || $value[1] < 0
                ) {
                    $fail('The :attribute must be an array with two entries both must be valid hour integers.')
                        ->translate();
                }
                break;
            case 'array:hour,hour,minute':
                if (count($value) !== 3
                    || ! is_int($value[0])
                    || ! is_int($value[1])
                    || ! is_int($value[2])
                    || $value[0] > 23 || $value[0] < 0
                    || $value[1] > 23 || $value[1] < 0
                    || $value[2] > 59 || $value[2] < 0
                ) {
                    $fail('The :attribute must be an array with three entries '
                        .'the first two entries must be valid hour integers, '
                        .'the third entry must be a valid minute integer.'
                    )
                        ->translate();
                }
                break;
            case 'array:weekday,time':
                if (count($value) !== 2
                    || ! is_int($value[0])
                    || ! is_string($value[1])
                    || $value[0] > 6 || $value[0] < 0
                    || ! preg_match('/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value[1])
                ) {
                    $fail('The :attribute must be an array with two entries, '
                        .'the first entry must be a weekday integer between 0 and 6 starting on sunday, '
                        .'the second entry must be a valid time string.'
                    )
                        ->translate();
                }
                break;
            case 'array:day,time':
                if (count($value) !== 2
                    || ! is_int($value[0])
                    || ! is_string($value[1])
                    || $value[0] > 31 || $value[0] < 1
                    || ! preg_match('/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value[1])
                ) {
                    $fail('The :attribute must be an array with two entries, '
                        .'the first entry must be a day of month integer, '
                        .'the second entry must be a valid time string.'
                    )
                        ->translate();
                }
                break;
            case 'array:day,day,time':
                if (count($value) !== 3
                    || ! is_int($value[0])
                    || ! is_int($value[1])
                    || ! is_string($value[2])
                    || $value[0] > 31 || $value[0] < 1
                    || $value[1] > 31 || $value[1] < 1
                    || ! preg_match('/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value[2])
                ) {
                    $fail('The :attribute must be an array with three entries, '
                        .'the first entry must be a day of month integer, '
                        .'the second entry must be a day of month integer, '
                        .'the third entry must be a valid time string.'
                    )
                        ->translate();
                }
                break;
            case 'array:month,day,time':
                if (count($value) !== 3
                    || ! is_int($value[0])
                    || ! is_int($value[1])
                    || ! is_string($value[2])
                    || $value[0] > 12 || $value[0] < 1
                    || $value[1] > 31 || $value[1] < 1
                    || ! preg_match('/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value[2])
                ) {
                    $fail('The :attribute must be an array with three entries, '
                        .'the first entry must be a month integer, '
                        .'the second entry must be a day of month integer, '
                        .'the third entry must be a valid time string.'
                    )
                        ->translate();
                }
                break;
            case 'array:weekdays':
                if (count($value) < 1 || count($value) > 7
                    || $value !== array_map('intval', $value)
                    || max(array_count_values($value)) > 1
                    || max($value) > 6 || min($value) < 0
                ) {
                    $fail('The :attribute must be an array with min 1 entry and max 7 entries, '
                        .'all entries must be a weekday integer between 0 and 6 starting on sunday '
                        .'and no duplicates are allowed.'
                    )
                        ->translate();
                }
                break;
            case 'array:time,time':
                if (count($value) !== 2
                    || ! is_string($value[0])
                    || ! is_string($value[1])
                    || ! preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value[0])
                    || ! preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $value[1])
                    || $value[1] > $value[0]
                ) {
                    $fail('The :attribute must be an array with two entries, '.
                        'both entries must be a valid time string.'
                    )->translate();
                }
                break;
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
