<?php

namespace FluxErp\Http\Requests;

class UpdateDiscountGroupRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:discount_groups,id',
            'name' => 'sometimes|required|string',
            'is_active' => 'boolean',
            'discounts' => 'array',
            'discounts.*' => 'integer|exists:discounts,id,deleted_at,NULL',
        ];
    }
}
