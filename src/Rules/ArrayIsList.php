<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ArrayIsList implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail($this->message())->translate();

            return;
        } elseif (! array_is_list($value)) {
            $fail($this->message())->translate();

            return;
        }

        foreach ($value as $item) {
            if (! is_string($item) && ! is_int($item) && ! is_numeric($item)) {
                $fail($this->message())->translate();

                return;
            }
        }
    }

    protected function message(): string
    {
        return 'Array must be a list';
    }
}
