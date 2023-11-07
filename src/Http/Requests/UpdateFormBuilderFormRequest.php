<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormBuilderFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_forms,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'ordering' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'details' => 'nullable|array',
            'options' => 'nullable|array',
            'extensions' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
}
