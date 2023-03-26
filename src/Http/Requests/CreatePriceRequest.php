<?php

namespace FluxErp\Http\Requests;

class CreatePriceRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id,deleted_at,NULL',
            'price_list_id' => 'required|integer|exists:price_lists,id,deleted_at,NULL',
            'price' => 'required|numeric',
        ];
    }
}
