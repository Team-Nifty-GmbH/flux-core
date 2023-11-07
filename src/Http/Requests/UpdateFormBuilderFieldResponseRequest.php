<?php

namespace FluxErp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormBuilderFieldResponseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_field_responses,id,deleted_at,NULL',
            'form_id' => 'required|exists:form_builder_forms,id',
            'field_id' => 'required|exists:form_builder_fields,id',
            'response_id' => 'required|exists:form_builder_responses,id',
            'response' => 'required|string',
        ];
    }
}
