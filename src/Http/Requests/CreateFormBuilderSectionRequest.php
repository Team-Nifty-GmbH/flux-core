<?php

namespace FluxErp\Http\Requests;

class CreateFormBuilderSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'form_id' => 'required|integer|exists:form_builder_forms,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'columns' => 'nullable|integer|min:1|max:12',
        ];
    }
}
