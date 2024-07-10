<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateAddressRuleset extends FluxRuleset
{
    protected static ?string $model = Address::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Address::class),
            ],
            'contact_id' => [
                'integer',
                new ExistsWithForeign(
                    foreignAttribute: 'client_id',
                    table: 'contacts',
                    baseTable: 'addresses'
                ),
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
            'email' => [
                'required_if_accepted:can_login',
                'nullable',
                'email',
            ],
            'password' => 'string|nullable',
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
            resolve_static(PermissionRuleset::class, 'getRules')
        );
    }
}
