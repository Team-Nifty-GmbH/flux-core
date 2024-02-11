<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use FluxErp\Rules\ModelExists;

class CreateStockPostingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:stock_postings,uuid',
            'warehouse_id' => [
                'required',
                'integer',
                new ModelExists(Warehouse::class),
            ],
            'product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'purchase_price' => 'required|numeric',
            'posting' => 'required|numeric',
            'description' => 'sometimes|required|string',
        ];
    }
}
