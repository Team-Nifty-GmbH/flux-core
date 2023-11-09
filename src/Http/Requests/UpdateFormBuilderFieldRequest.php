<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\FormBuilderTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormBuilderFieldRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_fields,id,deleted_at,NULL',
            'section_id' => 'required|integer|exists:form_builder_sections,id,deleted_at,NULL',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => [
                'required',
                Rule::in(FormBuilderTypeEnum::values())
            ],
            'ordering' => 'nullable|integer|min:0',
            'options' => 'nullable|array',
        ];
    }
}
