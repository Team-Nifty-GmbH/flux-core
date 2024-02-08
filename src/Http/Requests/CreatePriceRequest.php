<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;

class CreatePriceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:prices,uuid',
            'product_id' => [
                'required',
                'integer',
                new ModelExists(Product::class),
            ],
            'price_list_id' => [
                'required',
                'integer',
                new ModelExists(PriceList::class),
            ],
            'price' => 'required|numeric',
        ];
    }
}
