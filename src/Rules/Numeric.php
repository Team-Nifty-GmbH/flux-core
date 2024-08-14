<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class Numeric implements InvokableRule
{
    private string|int|null $min;

    private string|int|null $max;

    public function __construct(string|int|null $min = null, string|int|null $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
    {
        try {
            bcmul($value, '1', 9);
        } catch (\Throwable $e) {
            $fail('validation.numeric')->translate();

            return;
        }

        // 0 = equal, 1 = $value > $min, -1 $value < $min
        if (! is_null($this->min) && bccomp($value, $this->min, 9) === -1) {
            $fail('The :attribute must be at least '.$this->min.'.')->translate();

            return;
        }

        // 0 = equal, 1 = $value > $max, -1 $value < $max
        if (! is_null($this->max) && bccomp($value, $this->max, 9) === 1) {
            $fail('The :attribute must not be greater than '.$this->max.'.')->translate();
        }
    }
}
