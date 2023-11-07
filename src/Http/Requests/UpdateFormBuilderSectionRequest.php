<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormBuilderSectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_sections,id,deleted_at,NULL',
            'form_id' => 'required|exists:form_builder_forms,id',
            'name' => 'required|string|max:255',
            'ordering' => 'nullable|integer',
            'columns' => 'nullable|integer',
            'description' => 'nullable|string|max:255',
        ];
    }
}
