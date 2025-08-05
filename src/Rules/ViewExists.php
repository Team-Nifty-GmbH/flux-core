<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ViewExists implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! view()->exists($value)) {
            $fail('The given view does not exist.')->translate();
        }
    }
}
