<?php

namespace FluxErp\Http\Requests;

class CreateSerialNumberRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:serial_numbers,uuid',
            'serial_number_range_id' => 'integer|exists:serial_number_ranges,id,deleted_at,NULL',
            'product_id' => 'integer|exists:products,id,deleted_at,NULL',
            'address_id' => 'integer|exists:addresses,id,deleted_at,NULL',
            'order_position_id' => 'integer|exists:order_positions,id,deleted_at,NULL',
            'serial_number' => 'required|string',
        ];
    }
}
