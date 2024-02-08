<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;

class CreateProductBundleProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'bundle_product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'count' => 'required|numeric|gt:0',
        ];
    }
}
