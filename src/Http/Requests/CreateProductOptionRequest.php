<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rules\ModelExists;

class CreateProductOptionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:product_options,uuid',
            'product_option_group_id' => [
                'required',
                'integer',
                new ModelExists(ProductOptionGroup::class),
            ],
            'name' => 'required|string',
        ];
    }
}
