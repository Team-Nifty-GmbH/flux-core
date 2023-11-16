<?php

namespace FluxErp\Http\Requests;

class CreateFormBuilderResponseRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'form_id' => 'required|integer|exists:form_builder_forms,id,deleted_at,NULL',
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
        ];
    }
}
