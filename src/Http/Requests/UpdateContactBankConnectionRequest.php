<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;

class UpdateContactBankConnectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(ContactBankConnection::class),
            ],
            'contact_id' => [
                'integer',
                'nullable',
                new ModelExists(Contact::class),
            ],
            'iban' => ['string', new Iban()],
            'account_holder' => 'string|nullable',
            'bank_name' => 'string|nullable',
            'bic' => 'string|nullable',
        ];
    }
}
