<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFormBuilderFieldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'section_id' => 'required|exists:form_builder_sections,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
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
