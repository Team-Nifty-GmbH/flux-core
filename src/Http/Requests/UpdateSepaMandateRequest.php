<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateSepaMandateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:sepa_mandates,id,deleted_at,NULL',
            'client_id' => [
                'integer',
                (new ExistsWithIgnore('clients', 'id'))->whereNull('deleted_at'),
            ],
            'contact_id' => [
                'integer',
                (new ExistsWithIgnore('contacts', 'id'))->whereNull('deleted_at'),
            ],
            'contact_bank_connection_id' => [
                'integer',
                (new ExistsWithIgnore('contact_bank_connections', 'id'))->whereNull('deleted_at'),
            ],
            'signed_date' => 'sometimes|date|nullable',
        ];
    }
}
