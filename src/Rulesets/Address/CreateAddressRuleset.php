<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateAddressRuleset extends FluxRuleset
{
    protected static ?string $model = Address::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:addresses,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'contact_id' => [
                'required',
                'integer',
                new ExistsWithForeign(foreignAttribute: 'client_id', table: 'contacts'),
            ],
            'country_id' => [
                'integer',
                'nullable',
                new ModelExists(Country::class),
            ],
            'language_id' => [
                'integer',
                'nullable',
                new ModelExists(Language::class),
            ],
            'date_of_birth' => 'date|nullable',
            'department' => 'string|nullable',
            'login_name' => 'string|unique:addresses,login_name|nullable',
            'login_password' => 'string|nullable',
            'is_main_address' => 'boolean',
            'is_invoice_address' => 'boolean',
            'is_delivery_address' => 'boolean',
            'is_active' => 'boolean',
            'can_login' => 'boolean',
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(PostalAddressRuleset::class, 'getRules'),
            resolve_static(AddressTypeRuleset::class, 'getRules'),
            resolve_static(ContactOptionRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules'),
            [
                'contact_options.*.id' => 'exclude',
            ]
        );
    }
}
