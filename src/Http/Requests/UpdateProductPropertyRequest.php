<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\ProductProperty;
use FluxErp\Rules\ModelExists;

class UpdateProductPropertyRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ProductProperty::class),
            ],
            'name' => 'required|string',
        ];
    }
}
