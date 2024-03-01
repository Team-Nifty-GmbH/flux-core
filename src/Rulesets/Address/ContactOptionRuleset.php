<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\ContactOption;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class ContactOptionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'contact_options' => 'array',
            'contact_options.*.id' => [
                'integer',
                new ModelExists(ContactOption::class),
            ],
            'contact_options.*.type' => 'required|string',
            'contact_options.*.label' => 'required|string',
            'contact_options.*.value' => 'required|string',
            'contact_options.*.is_primary' => 'boolean',
        ];
    }
}
