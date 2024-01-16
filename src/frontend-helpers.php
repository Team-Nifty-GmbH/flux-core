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
        Livewire\Component $component,
        bool $skipRender = true
    ): void {
        if (! method_exists($component, 'notification')) {
            throw new InvalidArgumentException('Component does not have a notification method.');
        }

        switch (true) {
            case method_exists($exception, 'errors') && $errors = $exception->errors():
            case method_exists($exception, 'getResponse')
            && $errors = json_decode($exception->getResponse()->getContent(), true)['errors'] ?? []:

                $errors = \Illuminate\Support\Arr::dot($errors);
                foreach ($errors as $field => $messages) {
                    if (is_string($messages)) {
                        $component->notification()->error(__(\Illuminate\Support\Str::headline($field)), __($messages));
                        $component->addError($field, __($messages));

                        continue;
                    }

                    foreach ($messages as $message) {
                        $component->notification()->error(__(\Illuminate\Support\Str::headline($field)), __($message));
                        $component->addError($field, __($message));
                    }
                }

                break;
            default:
                $component->notification()->error($exception->getMessage());
                $component->addError('', $exception->getMessage());
        }

        if (! $exception instanceof \Illuminate\Validation\ValidationException) {
            \Illuminate\Support\Facades\Log::error(
                $exception->getMessage(),
                [
                    'exception' => $exception,
                    'backtrace' => $exception->getTraceAsString(),
                ]);
        }

        if ($skipRender) {
            $component->skipRender();
        }
    }
}
