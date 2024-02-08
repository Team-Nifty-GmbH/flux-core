<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;

class UpdatePriceListRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PriceList::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(PriceList::class),
            ],
            'name' => 'sometimes|required|string',
            'price_list_code' => 'sometimes|required|string',
            'is_net' => 'sometimes|boolean',
            'is_default' => 'boolean',

            'discount' => 'exclude_without:parent_id|exclude_if:parent_id,NULL|array',
            'discount.discount' => 'exclude_without:discount|present|numeric|nullable',
            'discount.is_percentage' => 'exclude_without:discount|required|boolean',
        ];
    }
}
