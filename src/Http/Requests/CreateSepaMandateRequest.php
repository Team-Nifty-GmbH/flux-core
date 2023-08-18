<?php

namespace FluxErp\Http\Requests;

class CreateSepaMandateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:sepa_mandates,uuid',
            'client_id' => 'required|integer|exists:clients,id,deleted_at,NULL',
            'contact_id' => 'required|integer|exists:contacts,id,deleted_at,NULL',
            'bank_connection_id' => 'required|integer|exists:bank_connections,id,deleted_at,NULL',
            'signed_date' => 'sometimes|date|nullable',
        ];
    }
}
