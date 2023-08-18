<?php

namespace FluxErp\Http\Requests;

class CreateProductOptionGroupRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:product_option_groups,uuid',
            'name' => 'required|string',
        ];
    }
}
