<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ClassExists implements ValidationRule
{
    private ?string $implements;

    private ?string $instanceOf;

    private array|string $uses;

    public function __construct(array|string $uses = [], ?string $instanceOf = null, ?string $implements = null)
    {
        $this->uses = (array) $uses;
        $this->instanceOf = $instanceOf;
        $this->implements = $implements;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! class_exists($value)) {
            $fail(sprintf('%s is no valid class.', $value))->translate();

            return;
        }

        if ($this->uses || $this->instanceOf || $this->implements) {
            $instance = app($value);
        }

        foreach ($this->uses as $use) {
            if (! in_array($use, class_uses_recursive($instance))) {
                $fail(sprintf('%s doesnt use %s.', $value, $use))->translate();
            }
        }

        if ($this->instanceOf && ! is_a($instance, $this->instanceOf, true)) {
            $fail(sprintf('%s is not a %s.', $value, $this->instanceOf))->translate();
        }

        if ($this->implements && ! is_a($instance, $this->implements, true)) {
            $fail(sprintf('%s doesnt implement %s.', $value, $this->implements))->translate();
        }
    }
}
