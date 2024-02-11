<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Discount;
use FluxErp\Rules\ModelExists;

class UpdateDiscountRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Discount::class),
            ],
            'discount' => 'required_with:is_percentage|numeric',
            'from' => 'nullable|date_format:Y-m-d H:i:s',
            'till' => 'nullable|date_format:Y-m-d H:i:s',
            'sort_number' => 'sometimes|integer|min:0',
            'is_percentage' => 'sometimes|boolean',
        ];
    }
}
