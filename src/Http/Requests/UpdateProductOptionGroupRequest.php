<?php

namespace FluxErp\Http\Requests;

class UpdateProductOptionGroupRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:product_option_groups,id,deleted_at,NULL',
            'name' => 'required|string',
        ];
    }
}
