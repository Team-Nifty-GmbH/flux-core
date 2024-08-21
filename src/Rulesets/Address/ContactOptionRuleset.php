<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\ContactOption;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ContactOptionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'contact_options' => 'array',
            'contact_options.*.id' => [
                'integer',
                app(ModelExists::class, ['model' => ContactOption::class]),
            ],
            'contact_options.*.type' => [
                'required',
                'string',
                Rule::in(['phone', 'email', 'website']),
            ],
            'contact_options.*.label' => 'required|string',
            'contact_options.*.value' => 'required|string',
            'contact_options.*.is_primary' => 'boolean',
        ];
    }
}
