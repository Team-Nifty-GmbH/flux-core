<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;

class UpdateProductBundleProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductBundleProduct::class),
            ],
            'bundle_product_id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'count' => 'sometimes|required|numeric|gt:0',
        ];
    }
}
