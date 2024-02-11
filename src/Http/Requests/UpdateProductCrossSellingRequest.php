<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rules\ModelExists;

class UpdateProductCrossSellingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductCrossSelling::class),
            ],
            'product_id' => [
                'integer',
                'nullable',
                new ModelExists(Product::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'order_column' => 'integer',
            'is_active' => 'boolean',

            'products' => 'array',
            'products.*' => [
                'integer',
                new ModelExists(Product::class),
            ],
        ];
    }
}
