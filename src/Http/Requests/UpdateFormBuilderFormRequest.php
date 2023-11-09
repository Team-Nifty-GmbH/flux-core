<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormBuilderFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_forms,id,deleted_at,NULL',
            'model_type' => 'required_with:model_id|string',
            'model_id' => 'required_with:model_type|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'options' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
}
