<?php

namespace FluxErp\Rules;

use Illuminate\Contracts\Validation\Rule;

class AvailableValidationRule implements Rule
{
    /**
     * @var string[]
     */
    public array $availableValidationRules;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->availableValidationRules = [
            'accepted',
            'accepted_if:',
            'active_url',
            'after:',
            'after_or_equal:',
            'alpha',
            'alpha_dash',
            'alpha_num',
            'before:',
            'before_or_equal:',
            'between:',
            'boolean',
            'date',
            'date_equals:',
            'date_format:',
            'declined',
            'declined_if:',
            'different:',
            'digits:',
            'digits_between:',
            'email',
            'ends_with:',
            'exclude_if:',
            'exclude_unless:',
            'exclude_with:',
            'exists:',
            'filled',
            'gt:',
            'gte:',
            'in:',
            'integer',
            'ip',
            'ipv4',
            'ipv6',
            'json',
            'lt:',
            'lte:',
            'mac_address',
            'max:',
            'min:',
            'multiple_of:',
            'not_in:',
            'not_regex:',
            'nullable',
            'numeric',
            'present',
            'prohibited_if:',
            'prohibited_unless',
            'regex:',
            'required',
            'required_if:',
            'required_unless:',
            'required_with:',
            'required_with_all:',
            'required_without:',
            'required_without_all:',
            'same:',
            'size:',
            'sometimes',
            'starts_with:',
            'string',
            'timezone',
            'unique:',
            'url',
            'uuid',
        ];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (! is_string($value) || str_ends_with($value, ':') || str_contains($value, '|')) {
            return false;
        }

        if (in_array($value, $this->availableValidationRules)) {
            return true;
        }

        $exploded = explode(':', $value);

        if (count($exploded) !== 2) {
            return false;
        }

        $validationRule = $exploded[0].':';

        return in_array($validationRule, $this->availableValidationRules);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return __('invalid validation rule');
    }
}
