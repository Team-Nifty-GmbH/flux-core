<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Rules\ModelExists;

class UpdateSepaMandateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SepaMandate::class),
            ],
            'contact_bank_connection_id' => [
                'integer',
                new ModelExists(ContactBankConnection::class),
            ],
            'signed_date' => 'date|nullable',
        ];
    }
}
