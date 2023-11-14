<?php

namespace FluxErp\Http\Requests;

class UpdateFormBuilderFieldResponseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_field_responses,id,deleted_at,NULL',
            'response' => 'required|string',
        ];
    }
}
