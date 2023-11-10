<?php

namespace FluxErp\Http\Requests;

class UpdateFormBuilderFormRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_forms,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|after:end_date|date',
            'is_active' => 'boolean',
        ];
    }
}
