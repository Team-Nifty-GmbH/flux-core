<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateSerialNumberRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:serial_numbers,id',
            'product_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('products', 'id'))->whereNull('deleted_at'),
            ],
            'address_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('addresses', 'id'))->whereNull('deleted_at'),
            ],
            'order_position_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('order_positions', 'id'))->whereNull('deleted_at'),
            ],
            'serial_number' => 'sometimes|required|string',
        ];
    }
}
