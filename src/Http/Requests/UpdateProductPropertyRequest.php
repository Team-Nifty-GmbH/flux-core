<?php

namespace FluxErp\Http\Requests;

class UpdateProductPropertyRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:product_properties,id,deleted_at,NULL',
            'name' => 'required|string',
        ];
    }
}
