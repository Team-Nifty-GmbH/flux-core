<?php

namespace FluxErp\Http\Requests;

class UpdateProductBundleProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:product_bundle_product,id',
            'bundle_product_id' => 'sometimes|required|integer|exists:products,id,deleted_at,NULL',
            'count' => 'sometimes|required|numeric|gt:0',
        ];
    }
}
