<?php

namespace FluxErp\Rulesets\ContactOption;

use FluxErp\Models\Address;
use FluxErp\Models\ContactOption;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateContactOptionRuleset extends FluxRuleset
{
    protected static ?string $model = ContactOption::class;

    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                new ModelExists(Address::class),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['phone', 'email', 'website']),
            ],
            'label' => 'required|string',
            'value' => 'required|string',
        ];
    }
}
