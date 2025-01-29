<?php

namespace FluxErp\Support\Calculation;

class Rounding
{
    public static function round(string|float|int|null $value, int $precision = 2): string
    {
        return is_null($value)
            ? 0
            : bcdiv(
                bcround(bcmul($value, pow(10, $precision))),
                pow(10, $precision),
                max(0, $precision)
            );
    }

    public static function ceil(string|float|int|null $value, int $precision = 0): string
    {
        return is_null($value)
            ? 0
            : bcdiv(
                bcceil(bcmul($value, pow(10, $precision))),
                pow(10, $precision),
                max(0, $precision)
            );
    }

    public static function floor(string|float|int|null $value, int $precision = 0): string
    {
        return is_null($value)
            ? 0
            : bcdiv(
                bcfloor(bcmul($value, pow(10, $precision))),
                pow(10, $precision),
                max(0, $precision)
            );
    }

    /**
     * Round value to the nearest multiple of the given number
     */
    public static function nearest(
        int $number,
        string|float|int $value,
        int $precision = 2,
        string $mode = 'round'
    ): string {
        $number = abs($number);
        $value = bcmul($value, pow(10, $precision), 0);
        $mod = bcmod($value, $number);

        if ($value[0] !== '-') {
            $value = match ($mode) {
                'ceil' => bcadd($value, bcsub($number, $mod, 0), 0),
                'floor' => bcsub($value, $mod, 0),
                default => $mod >= bcdiv($number, 2)
                    ? bcadd($value, bcsub($number, $mod, 0), 0)
                    : bcsub($value, $mod, 0),
            };
        } else {
            $mod = bcmul($mod, -1);
            $value = match ($mode) {
                'ceil' => bcadd($value, $mod, 0),
                'floor' => bcsub($value, bcsub($number, $mod, 0), 0),
                default => $mod >= bcdiv($number, 2)
                    ? bcadd($value, $mod, 0)
                    : bcsub($value, bcsub($number, $mod, 0), 0),
            };
        }

        return bcdiv($value, pow(10, $precision), max(0, $precision));
    }

    /**
     * Round value so that it always ends with the given number
     */
    public static function end(
        int $number,
        string|float|int $value,
        int $precision = 2,
        string $mode = 'round'
    ): string {
        $number = abs($number);
        $length = strlen($number);

        $powValue = bcmul($value, pow(10, $precision), 1);
        $end = substr($powValue, -($length + 2));

        if (bccomp($end, $number) === 0) {
            return bcadd($value, 0, $precision);
        }

        if (! in_array($mode, ['ceil', 'floor'])) {
            $modifier = 5 . str_repeat(0, $length - 1);

            if (bccomp($number, $modifier) < 1) {
                $min = bcadd($number, 1, 0);
                $max = bcadd($number, $modifier, 0);

                $mode = bccomp($end, $min) >= 0 && bccomp($end, $max) === -1 ? 'floor' : 'ceil';
            } else {
                $min = bcsub($number, $modifier, 0);

                $mode = bccomp($end, $min) >= 0 && bccomp($end, $number) === -1 ? 'ceil' : 'floor';
            }
        }

        if ($mode == 'ceil') {
            if (bccomp($end, $number) === 1) {
                $powValue = $powValue[0] !== '-' ?
                    bcadd($powValue, 1 . str_repeat(0, $length), 1) :
                    bcsub($powValue, 1 . str_repeat(0, $length), 1);
            }
        } else {
            if (bccomp($end, $number) === -1) {
                $powValue = $powValue[0] !== '-' ?
                    bcsub($powValue, 1 . str_repeat(0, $length), 1) :
                    bcadd($powValue, 1 . str_repeat(0, $length), 1);
            }
        }

        $powValue = substr_replace($powValue, $number, -($length + 2), $length);

        return bcdiv($powValue, pow(10, $precision), max(0, $precision));
    }
}
