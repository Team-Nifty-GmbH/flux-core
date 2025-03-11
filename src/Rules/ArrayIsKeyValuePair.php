<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\Rule;

class ArrayIsKeyValuePair implements Rule
{
    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return __('The Array must consist of key value string pairs.');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (! is_array($value) || count($value) < 1) {
            return false;
        }

        foreach ($value as $key => $item) {
            if (! is_string($key) || ! is_string($item)) {
                return false;
            }

            if (! $key || ! $item) {
                return false;
            }
        }

        return true;
    }
}
