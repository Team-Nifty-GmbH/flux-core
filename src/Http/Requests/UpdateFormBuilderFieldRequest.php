<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormBuilderFieldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_fields,id,deleted_at,NULL',
            'section_id' => 'nullable|exists:form_builder_sections,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => [
                'required',
                Rule::in([
                    'text',
                    'textarea',
                    'select',
                    'checkbox',
                    'radio',
                    'date',
                    'time',
                    'datetime',
                    'number',
                    'email',
                    'password',
                    'range',
                ])
            ],
            'ordering' => 'nullable|integer',
            'options' => 'nullable|array',
        ];
    }
}
