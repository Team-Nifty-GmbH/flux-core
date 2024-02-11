<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;

class CreateProductCrossSellingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:product_cross_sellings,uuid',
            'product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'name' => 'required|string|max:255',
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
