<?php

namespace FluxErp\Http\Requests;

class CreateProductBundleProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id,deleted_at,NULL',
            'bundle_product_id' => 'required|integer|exists:products,id,deleted_at,NULL',
            'count' => 'required|numeric|gt:0',
        ];
    }
}
