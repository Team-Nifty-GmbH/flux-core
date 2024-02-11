<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;

class CreatePriceListRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:price_lists,uuid',
            'parent_id' => [
                'nullable',
                'integer',
                new ModelExists(PriceList::class),
            ],
            'name' => 'required|string',
            'price_list_code' => 'required|string|unique:price_lists,price_list_code',
            'is_net' => 'required|boolean',
            'is_default' => 'boolean',

            'discount' => 'exclude_without:parent_id|exclude_if:parent_id,NULL|array',
            'discount.discount' => 'exclude_without:discount|present|numeric|nullable',
            'discount.is_percentage' => 'exclude_without:discount|required|boolean',
        ];
    }
}
