<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Product;

class CreateCommissionRateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
            'contact_id' => [
                'required_without_all:category_id,product_id',
                'integer',
                'nullable',
                'exists:contacts,id,deleted_at,NULL',
            ],
            'category_id' => [
                'required_without_all:contact_id,product_id',
                'integer',
                'nullable',
                'exists:categories,id,model_type,' . Product::class,
            ],
            'product_id' => [
                'required_without_all:contact_id,category_id',
                'integer',
                'nullable',
                'exists:products,id,deleted_at,NULL',
            ],
            'commission_rate' => 'required|numeric|lt:1|min:0',
        ];
    }
}
