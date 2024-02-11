<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use FluxErp\Rules\ModelExists;

class UpdateDiscountGroupRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(DiscountGroup::class),
            ],
            'name' => 'sometimes|required|string',
            'is_active' => 'boolean',
            'discounts' => 'array',
            'discounts.*' => [
                'integer',
                new ModelExists(Discount::class),
            ],
        ];
    }
}
