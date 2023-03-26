<?php

if (! function_exists('format_number')) {
    function format_number(
        string|int|float|null $number,
        int $style = \NumberFormatter::DECIMAL,
        int $maxFractionDigits = 2
    ): float|bool|int|string|null {
        if (! is_numeric($number)) {
            return $number;
        }

        $numberFormatter = numfmt_create(app()->getLocale(), $style);
        numfmt_set_attribute($numberFormatter, \NumberFormatter::MAX_FRACTION_DIGITS, $maxFractionDigits);

        return numfmt_format(
            $numberFormatter,
            $number
        );
    }
}
