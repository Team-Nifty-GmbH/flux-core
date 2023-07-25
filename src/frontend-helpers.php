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

if (! function_exists('exception_to_notifications')) {
    function exception_to_notifications(
        Exception $exception,
        \Livewire\Component $component
    ): void
    {
        if (! method_exists($component, 'notification')) {
            throw new InvalidArgumentException('Component does not have a notification method.');
        }

        if (method_exists($exception, 'errors')) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $component->notification()->error($field, $message);
                }
            }
        } else {
            $component->notification()->error($exception->getMessage());
        }

        $component->skipRender();
    }
}
