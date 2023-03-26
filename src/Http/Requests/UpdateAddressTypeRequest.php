<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\AddressType;
use FluxErp\Rules\UniqueInFieldDependence;

class UpdateAddressTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:address_types,id,deleted_at,NULL',
            'address_type_code' => [
                'string',
                'nullable',
                new UniqueInFieldDependence(AddressType::class, 'client_id'),
            ],
            'name' => 'sometimes|required|string',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
