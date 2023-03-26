<?php

namespace FluxErp\Http\Requests;

use Illuminate\Validation\Rule;

class CreateAddressTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|integer|exists:clients,id,deleted_at,NULL',
            'address_type_code' => [
                'string',
                'nullable',
                Rule::unique('address_types')->where('client_id', $data['client_id'] ?? null),
            ],
            'name' => 'required|string',
            'is_locked' => 'boolean',
            'is_unique' => 'boolean',
        ];
    }
}
