<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class Numeric implements ValidationRule
{
    private string|int|float|null $max;

    private string|int|float|null $min;

    public function __construct(string|int|float|null $min = null, string|int|float|null $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            bcmul($value, '1', 9);
        } catch (Throwable) {
            $fail('validation.numeric')->translate();

            return;
        }

        // 0 = equal, 1 = $value > $min, -1 $value < $min
        if (! is_null($this->min) && bccomp($value, $this->min, 9) === -1) {
            $fail('validation.min.numeric')->translate(['min' => $this->min]);

            return;
        }

        // 0 = equal, 1 = $value > $max, -1 $value < $max
        if (! is_null($this->max) && bccomp($value, $this->max, 9) === 1) {
            $fail('validation.max.numeric')->translate(['max' => $this->max]);
        }
    }
}
