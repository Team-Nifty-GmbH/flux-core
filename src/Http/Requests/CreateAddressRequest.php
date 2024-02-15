<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Country;
use FluxErp\Models\Language;
use FluxErp\Models\Tag;
use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ModelExists;

class CreateAddressRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new Address())->hasAdditionalColumnsValidationRules(),
            $this->postalAddressRules(),
            [
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

                'address_types' => 'array',
                'address_types.*' => [
                    'sometimes',
                    'distinct',
                    'integer',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'address_types'),
                ],

                'contact_options' => 'array',
                'contact_options.*' => 'array',
                'contact_options.*.type' => 'required|string',
                'contact_options.*.label' => 'required|string',
                'contact_options.*.value' => 'required|string',
                'contact_options.*.is_primary' => 'boolean',

                'tags' => 'array',
                'tags.*' => [
                    'required',
                    'integer',
                    (new ModelExists(Tag::class))->where('type', Address::class),
                ],
            ],
        );
    }

    public function postalAddressRules(): array
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
            'zip' => 'string|nullable',
            'city' => 'string|nullable',
            'street' => 'string|nullable',

            'url' => 'string|nullable',
            'email' => 'email|nullable',
            'phone' => 'string|nullable',
        ];
    }
}
