<?php

namespace FluxErp\Http\Requests;

class UpdateFormBuilderFormRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_forms,id,deleted_at,NULL',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'start_date' => 'present|nullable|datetime:Y-m-d H:i:s',
            'end_date' => 'present|nullable|after:start_date|datetime:Y-m-d H:i:s',
            'is_active' => 'boolean',
        ];
    }
}
