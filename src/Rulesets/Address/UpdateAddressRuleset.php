<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Address\AdvertisingState;

class UpdateAddressRuleset extends FluxRuleset
{
    protected static ?string $model = Address::class;

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

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Address::class]),
            ],
            'contact_id' => [
                'integer',
                app(ExistsWithForeign::class, [
                    'foreignAttribute' => 'tenant_id',
                    'table' => 'contacts',
                    'baseTable' => 'addresses',
                ]),
            ],
            'country_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Country::class]),
            ],
            'language_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'advertising_state' => [
                'string',
                ValidStateRule::make(AdvertisingState::class),
            ],
            'date_of_birth' => 'date|nullable',
            'department' => 'string|max:255|nullable',
            'email' => [
                'required_if_accepted:can_login',
                'email',
                'max:255',
                'nullable',
            ],
            'search_aliases' => [
                'array',
                'nullable',
            ],
            'search_aliases.*' => 'string|max:255|distinct:ignore_case',
            'password' => 'string|max:255|nullable',
            'is_main_address' => 'boolean',
            'is_invoice_address' => 'boolean',
            'is_delivery_address' => 'boolean',
            'is_active' => 'boolean',
            'can_login' => 'boolean',
        ];
    }
}
