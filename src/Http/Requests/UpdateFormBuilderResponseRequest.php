<?php

namespace FluxErp\Http\Requests;

class UpdateFormBuilderResponseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:form_builder_responses,id,deleted_at,NULL',
            'form_id' => 'required|integer|exists:form_builder_forms,id,deleted_at,NULL',
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
        ];
    }
}
