<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormBuilderResponseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_responses,id,deleted_at,NULL',
            'form_id' => 'required|exists:form_builder_forms,id,deleted_at,NULL',
            'user_id' => 'required|exists:users,id,deleted_at,NULL',
        ];
    }
}
