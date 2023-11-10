<?php

namespace FluxErp\Http\Requests;

class CreateFormBuilderFormRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => 'required_with:model_id|string',
            'model_id' => 'required_with:model_type|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|after:start_date|date',
            'is_active' => 'boolean',
        ];
    }
}
