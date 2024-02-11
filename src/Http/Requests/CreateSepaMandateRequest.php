<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rules\ModelExists;

class CreateSepaMandateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:sepa_mandates,uuid',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'contact_id' => [
                'required',
                'integer',
                new ModelExists(Contact::class),
            ],
            'contact_bank_connection_id' => [
                'required',
                'integer',
                new ModelExists(ContactBankConnection::class),
            ],
            'signed_date' => 'sometimes|date|nullable',
        ];
    }
}
