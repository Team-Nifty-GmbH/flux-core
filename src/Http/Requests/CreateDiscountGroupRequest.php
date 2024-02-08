<?php

namespace FluxErp\Http\Requests;

class CreateDiscountGroupRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:discount_groups,uuid',
            'name' => 'required|string',
            'is_active' => 'boolean',
            'discounts' => 'array',
            'discounts.*' => 'integer|exists:discounts,id,deleted_at,NULL',
        ];
    }
}
