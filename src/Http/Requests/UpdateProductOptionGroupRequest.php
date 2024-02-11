<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rules\ModelExists;

class UpdateProductOptionGroupRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductOptionGroup::class),
            ],
            'name' => 'required|string',

            'product_options' => 'array',
            'product_options.*.id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(ProductOption::class),
            ],
            'product_options.*.name' => 'required|string',
        ];
    }
}
