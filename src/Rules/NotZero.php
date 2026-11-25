<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class NotZero implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            if (bccomp((string) $value, '0', 9) === 0) {
                $fail('The :attribute must not be zero.');
            }
        } catch (Throwable) {
        }
    }
}
