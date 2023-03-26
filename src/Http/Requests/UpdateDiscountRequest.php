<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateDiscountRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:discounts,id,deleted_at,NULL',
            'order_position_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('order_positions', 'id'))->whereNull('deleted_at'),
            ],
            'sort_number' => 'sometimes|integer|min:0',
            'discount' => 'required_with:is_percentage|numeric',
            'is_percentage' => 'sometimes|boolean',
        ];
    }
}
