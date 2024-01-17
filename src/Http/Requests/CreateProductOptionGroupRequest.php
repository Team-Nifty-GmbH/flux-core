<?php

namespace FluxErp\Http\Requests;

class CreateProductOptionGroupRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:product_option_groups,uuid',
            'name' => 'required|string',
            'product_options' => 'array',
            'product_options.*.name' => 'required|string',
        ];
    }
}
