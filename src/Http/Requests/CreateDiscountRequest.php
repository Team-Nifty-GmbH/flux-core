<?php

namespace FluxErp\Http\Requests;

class CreateDiscountRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sort_number' => 'integer|min:0',
            'order_position_id' => 'required|integer|exists:order_positions,id,deleted_at,NULL',
            'discount' => 'required|numeric',
            'is_percentage' => 'required|boolean',
        ];
    }
}
