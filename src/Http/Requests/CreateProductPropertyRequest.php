<?php

namespace FluxErp\Http\Requests;

class CreateProductPropertyRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:product_properties,uuid',
            'name' => 'required|string',
        ];
    }
}
