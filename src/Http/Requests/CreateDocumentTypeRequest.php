<?php

namespace FluxErp\Http\Requests;

/**
 * @deprecated
 */
class CreateDocumentTypeRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:document_types,uuid',
            'client_id' => 'required|integer|exists:clients,id,deleted_at,NULL',
            'name' => 'required|string',
            'description' => 'string|nullable',
            'additional_header' => 'string|nullable',
            'additional_footer' => 'string|nullable',
            'is_active' => 'boolean',
        ];
    }
}
