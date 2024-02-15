<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;

class UpdateTagRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Tag::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'color' => 'nullable|hex_color',
            'order_column' => 'nullable|integer|min:0',
        ];
    }
}
