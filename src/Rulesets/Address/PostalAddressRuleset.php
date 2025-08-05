<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Rules\StringOrInteger;
use FluxErp\Rulesets\FluxRuleset;

class PostalAddressRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = Address::class;

    public function rules(): array
    {
        return [
            'company' => 'string|max:255|nullable',
            'title' => 'string|max:255|nullable',
            'salutation' => 'string|max:255|nullable',
            'firstname' => 'string|max:255|nullable',
            'lastname' => 'string|max:255|nullable',
            'addition' => 'string|max:255|nullable',
            'mailbox' => 'string|max:255|nullable',
            'mailbox_city' => 'string|max:255|nullable',
            'mailbox_zip' => 'string|max:255|nullable',
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
            'city' => 'string|max:255|nullable',
            'street' => 'string|max:255|nullable',

            'url' => 'string|max:255|nullable',
            'email_primary' => 'email|max:255|nullable',
            'phone' => 'string|max:255|nullable',
            'phone_mobile' => 'string|max:255|nullable',
            'has_formal_salutation' => 'boolean|nullable',
        ];
    }
}
