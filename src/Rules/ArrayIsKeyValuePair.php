<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ArrayIsKeyValuePair implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || count($value) < 1) {
            $fail($this->message())->translate();

            return;
        }

        foreach ($value as $key => $item) {
            if (! is_string($key) || ! is_string($item)) {
                $fail($this->message())->translate();

                return;
            }

            if (! $key || ! $item) {
                $fail($this->message())->translate();

                return;
            }
        }
    }

    protected function message(): string
    {
        return 'The array must consist of key value string pairs.';
    }
}
