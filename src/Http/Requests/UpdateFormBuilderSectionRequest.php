<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormBuilderSectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_sections,id',
            'form_id' => 'required|exists:form_builder_forms,id',
            'name' => 'required|string|max:255',
            'ordering' => 'nullable|integer',
            'columns' => 'nullable|integer',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'aside' => 'nullable|boolean',
            'compact' => 'nullable|boolean',
        ];
    }
}
