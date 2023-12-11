<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class ClassExists implements InvokableRule
{
    private array|string $uses;

    private ?string $instanceOf;

    private ?string $implements;

    public function __construct(array|string $uses = [], ?string $instanceOf = null, ?string $implements = null)
    {
        $this->uses = (array) $uses;
        $this->instanceOf = $instanceOf;
        $this->implements = $implements;
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
        if (! class_exists($value)) {
            $fail(sprintf('%s is no valid class.', $value))->translate();

            return;
        }

        if ($this->uses || $this->instanceOf || $this->implements) {
            $instance = new $value();
        }

        foreach ($this->uses as $use) {
            if (! in_array($use, class_uses($instance))) {
                $fail(sprintf('%s doesnt use %s.', $value, $use))->translate();
            }
        }

        if ($this->instanceOf && ! is_a($instance, $this->instanceOf, true)) {
            $fail(sprintf('%s is not a %s.', $value, $this->instanceOf))->translate();
        }

        if ($this->implements && ! in_array($this->implements, class_implements($instance))) {
            $fail(sprintf('%s doesnt implement %s.', $value, $this->implements))->translate();
        }
    }
}
