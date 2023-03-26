<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\Rule;

class ArrayIsList implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (! is_array($value)) {
            return false;
        } elseif (! array_is_list($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (! is_string($item) && ! is_int($item) && ! is_numeric($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return __('Array must be a list');
    }
}
