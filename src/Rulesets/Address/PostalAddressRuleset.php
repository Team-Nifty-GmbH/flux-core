<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Rules\StringOrInteger;
use FluxErp\Rulesets\FluxRuleset;

class PostalAddressRuleset extends FluxRuleset
{
    protected static ?string $model = Address::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'company' => 'string|nullable',
            'title' => 'string|nullable',
            'salutation' => 'string|nullable',
            'firstname' => 'string|nullable',
            'lastname' => 'string|nullable',
            'addition' => 'string|nullable',
            'mailbox' => 'string|nullable',
            'mailbox_city' => 'string|nullable',
            'mailbox_zip' => 'string|nullable',
            'latitude' => [
                'nullable',
                'regex:/^[-]?(([0-8]?[0-9](\.\d+)?)|(90(\.0+)?))$/',
            ],
            'longitude' => [
                'nullable',
                'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.\d+)?)|(180(\.0+)?))$/',
            ],
            'zip' => [
                'nullable',
                app(StringOrInteger::class),
            ],
            'city' => 'string|nullable',
            'street' => 'string|nullable',

            'url' => 'string|nullable',
            'email_primary' => 'email|nullable',
            'phone' => 'string|nullable',
            'has_formal_salutation' => 'boolean|nullable',
        ];
    }
}
