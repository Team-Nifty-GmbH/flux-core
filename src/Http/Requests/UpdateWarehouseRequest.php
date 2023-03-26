<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateWarehouseRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:warehouses,id,deleted_at,NULL',
            'address_id' => [
                'integer',
                (new ExistsWithIgnore('addresses', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'sometimes|required|string',
        ];
    }
}
