<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\FormBuilderTypeEnum;
use Illuminate\Validation\Rule;

class UpdateFormBuilderFieldRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:form_builder_fields,id,deleted_at,NULL',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => [
                'sometimes',
                'required',
                Rule::in(FormBuilderTypeEnum::values()),
            ],
            'options' => 'nullable|array',
            'option_values' => 'nullable|array',
        ];
    }
}
