<?php

namespace FluxErp\Http\Requests;

class UpdateProductBundleProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:product_bundle_product,id',
            'bundle_product_id' => 'integer|exists:products,id,deleted_at,NULL',
            'count' => 'numeric|gt:0',
        ];
    }
}
