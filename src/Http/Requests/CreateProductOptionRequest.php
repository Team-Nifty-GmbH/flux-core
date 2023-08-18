<?php

namespace FluxErp\Http\Requests;

class CreateProductOptionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:product_options,uuid',
            'product_option_group_id' => 'required|integer|exists:product_option_groups,id,deleted_at,NULL',
            'name' => 'required|string',
        ];
    }
}
