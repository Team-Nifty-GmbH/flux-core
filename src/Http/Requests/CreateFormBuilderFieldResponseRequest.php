<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormBuilderFieldResponseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'form_id' => 'required|integer|exists:form_builder_forms,id,deleted_at,NULL',
            'field_id' => 'required|integer|exists:form_builder_fields,id,deleted_at,NULL',
            'response_id' => 'required|integer|exists:form_builder_responses,id,deleted_at,NULL',
            'response' => 'required|string',
        ];
    }
}
