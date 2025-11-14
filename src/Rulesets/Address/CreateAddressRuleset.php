<?php

namespace FluxErp\Rulesets\Address;

use FluxErp\Models\Address;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use FluxErp\Models\Tenant;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Address\AdvertisingState;
use Illuminate\Validation\Rule;

class CreateAddressRuleset extends FluxRuleset
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
            resolve_static(PermissionRuleset::class, 'getRules'),
            [
                'contact_options.*.id' => 'exclude',
            ]
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:addresses,uuid',
            'tenant_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Tenant::class]),
            ],
            'contact_id' => [
                'required',
                'integer',
                app(ExistsWithForeign::class, ['foreignAttribute' => 'tenant_id', 'table' => 'contacts']),
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
                'nullable',
                'email',
                'max:255',
                Rule::unique('addresses', 'email')
                    ->whereNull('deleted_at'),
            ],
            'password' => 'string|max:255|nullable',
            'search_aliases' => [
                'array',
                'nullable',
            ],
            'search_aliases.*' => 'string|max:255|distinct:ignore_case',
            'is_main_address' => 'boolean',
            'is_invoice_address' => 'boolean',
            'is_delivery_address' => 'boolean',
            'is_active' => 'boolean',
            'can_login' => 'boolean',
        ];
    }
}
