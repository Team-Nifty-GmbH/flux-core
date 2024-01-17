<?php

namespace FluxErp\Http\Requests;

class UpdateProductOptionGroupRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:product_option_groups,id,deleted_at,NULL',
            'name' => 'required|string',

            'product_options' => 'array',
            'product_options.*.id' => 'sometimes|required|integer|exists:product_options,id',
            'product_options.*.name' => 'required|string',
        ];
    }
}
