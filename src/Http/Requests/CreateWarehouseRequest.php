<?php

namespace FluxErp\Http\Requests;

class CreateWarehouseRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:warehouses,uuid',
            'address_id' => 'integer|nullable|exists:addresses,id,deleted_at,NULL',
            'name' => 'required|string',
            'is_default' => 'boolean',
        ];
    }
}
