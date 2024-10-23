<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StringOrInteger implements ValidationRule
{
    public function __construct(protected bool $unsigned = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_int($value)) {
            $fail('validation.string_or_integer')->translate();
        }

        if ($this->unsigned && is_int($value) && bccomp($value, 0) === -1) {
            $fail('validation.string_or_integer_unsigned')->translate();
        }
    }
}
