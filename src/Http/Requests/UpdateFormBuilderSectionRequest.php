<?php

namespace FluxErp\Http\Requests;

class UpdateFormBuilderSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:form_builder_sections,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ordering' => 'nullable|integer|min:0',
            'columns' => 'nullable|integer|min:1|max:12',
        ];
    }
}
