<?php

namespace FluxErp\Http\Requests;

class CreateProductPropertyRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:product_properties,uuid',
            'name' => 'required|string',
        ];
    }
}
