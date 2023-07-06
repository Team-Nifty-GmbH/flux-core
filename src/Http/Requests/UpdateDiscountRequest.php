<?php

namespace FluxErp\Http\Requests;

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
            'discount' => 'required_with:is_percentage|numeric',
            'from' => 'nullable|date_format:Y-m-d H:i:s',
            'till' => 'nullable|date_format:Y-m-d H:i:s',
            'sort_number' => 'sometimes|integer|min:0',
            'is_percentage' => 'sometimes|boolean',
        ];
    }
}
