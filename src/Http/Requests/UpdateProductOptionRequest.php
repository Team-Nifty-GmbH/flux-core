<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Rules\ModelExists;

class UpdateProductOptionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductOption::class),
            ],
            'product_option_group_id' => [
                'integer',
                new ModelExists(ProductOptionGroup::class),
            ],
            'name' => 'sometimes|required|string',
        ];
    }
}
