<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormBuilderSectionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'form_id' => 'required|exists:form_builder_forms,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'ordering' => 'nullable|integer|min:0',
            'columns' => 'nullable|integer|min:1|max:12',
            'description' => 'nullable|string',
        ];
    }
}
