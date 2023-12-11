<?php

namespace FluxErp\Http\Requests;

class CreateTagRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'string',
                'max:255',
            ],
            'type' => 'string|max:255',
            'color' => 'nullable|hex_color',
            'order_column' => 'nullable|integer',
        ];
    }
}
