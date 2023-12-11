<?php

namespace FluxErp\Http\Requests;

class UpdateTagRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:tags,id',
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'color' => 'nullable|hex_color',
            'order_column' => 'nullable|integer',
        ];
    }
}
