<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;

class UpdatePriceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Price::class),
            ],
            'product_id' => [
                'integer',
                new ModelExists(Product::class),
            ],
            'price_list_id' => [
                'integer',
                new ModelExists(PriceList::class),
            ],
            'price' => 'sometimes|numeric',
        ];
    }
}
