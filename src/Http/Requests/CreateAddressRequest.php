<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Address;
use FluxErp\Rules\ExistsWithForeign;

class CreateAddressRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Address())->hasAdditionalColumnsValidationRules(),
            [
                'client_id' => 'required|integer|exists:clients,id,deleted_at,NULL',
                'contact_id' => [
                    'required',
                    'integer',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'contacts'),
                ],
                'country_id' => [
                    'integer',
                    'nullable',
                    'exists:countries,id,deleted_at,NULL',
                ],
                'language_id' => [
                    'integer',
                    'nullable',
                    'exists:languages,id,deleted_at,NULL',
                ],
                'company' => 'sometimes|string|nullable',
                'title' => 'sometimes|string|nullable',
                'salutation' => 'sometimes|string|nullable',
                'firstname' => 'sometimes|string|nullable',
                'lastname' => 'sometimes|string|nullable',
                'addition' => 'sometimes|string|nullable',
                'mailbox' => 'sometimes|string|nullable',
                'latitude' => [
                    'sometimes',
                    'nullable',
                    'regex:/^[-]?(([0-8]?[0-9](\.\d+)?)|(90(\.0+)?))$/',
                ],
                'longitude' => [
                    'sometimes',
                    'nullable',
                    'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.\d+)?)|(180(\.0+)?))$/',
                ],
                'zip' => 'sometimes|string|nullable',
                'city' => 'sometimes|string|nullable',
                'street' => 'sometimes|string|nullable',
                'url' => 'sometimes|string|nullable',
                'date_of_birth' => 'sometimes|date|nullable',
                'department' => 'sometimes|string|nullable',
                'login_name' => 'sometimes|string|unique:addresses,login_name|nullable',
                'login_password' => 'sometimes|string|nullable',
                'is_main_address' => 'sometimes|boolean',
                'is_active' => 'sometimes|boolean',
                'can_login' => 'sometimes|boolean',
                'address_types' => 'sometimes|array',
                'address_types.*' => [
                    'sometimes',
                    'distinct',
                    'integer',
                    new ExistsWithForeign(foreignAttribute: 'client_id', table: 'address_types'),
                ],
                'contact_options' => 'sometimes|array',
                'contact_options.*' => 'array',
                'contact_options.*.type' => 'required|string',
                'contact_options.*.label' => 'required|string',
                'contact_options.*.value' => 'required|string',
                'contact_options.*.is_primary' => 'sometimes|required|boolean',
            ],
        );
    }
}
