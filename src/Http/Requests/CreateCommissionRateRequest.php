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
                'integer',
                'nullable',
                'exists:contacts,id,deleted_at,NULL',
            ],
            'category_id' => [
                'exclude_unless:product_id,null',
                'integer',
                'nullable',
                'exists:categories,id,model_type,' . Product::class,
            ],
            'product_id' => [
                'integer',
                'nullable',
                'exists:products,id,deleted_at,NULL',
            ],
            'commission_rate' => 'required|numeric|lt:1|min:0',
        ];
    }
}
