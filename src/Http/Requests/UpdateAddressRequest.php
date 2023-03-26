<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithForeign;
use FluxErp\Rules\ExistsWithIgnore;

class UpdateAddressRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:addresses,id,deleted_at,NULL',
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
                (new ExistsWithIgnore('countries', 'id'))->whereNull('deleted_at'),
            ],
            'language_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('languages', 'id'))->whereNull('deleted_at'),
            ],
            'company' => 'string|nullable',
            'title' => 'string|nullable',
            'salutation' => 'string|nullable',
            'firstname' => 'string|nullable',
            'lastname' => 'string|nullable',
            'addition' => 'string|nullable',
            'mailbox' => 'string|nullable',
            'latitude' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9](\.\d+)?)|(90(\.0+)?))$/'],
            'longitude' => [
                'nullable',
                'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.\d+)?)|(180(\.0+)?))$/',
            ],
            'zip' => 'string|nullable',
            'city' => 'string|nullable',
            'street' => 'string|nullable',
            'url' => 'string|nullable',
            'date_of_birth' => 'date|nullable',
            'department' => 'string|nullable',
            'login_name' => 'string|nullable',
            'login_password' => 'string|nullable',
            'is_main_address' => 'sometimes|required|boolean',
            'is_active' => 'boolean',
            'can_login' => 'boolean',
            'address_types' => 'sometimes|required|array',
            'address_types.*' => [
                'distinct',
                'integer',
                new ExistsWithForeign(
                    foreignAttribute: 'client_id',
                    table: 'address_types',
                    baseTable: 'addresses'
                ),
            ],
            'contact_options' => 'sometimes|array',
            'contact_options.*' => 'array',
            'contact_options.*.id' => 'sometimes|integer|exists:contact_options,id',
            'contact_options.*.type' => 'required|string',
            'contact_options.*.label' => 'required|string',
            'contact_options.*.value' => 'required|string',
            'contact_options.*.is_primary' => 'sometimes|required|boolean',
        ];
    }
}
