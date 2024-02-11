<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Contact;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;

class CreateContactBankConnectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:bank_connections,uuid',
            'contact_id' => [
                'integer',
                'nullable',
                new ModelExists(Contact::class),
            ],
            'iban' => ['required', 'string', new Iban()],
            'account_holder' => 'string|nullable',
            'bank_name' => 'string|nullable',
            'bic' => 'string|nullable',
        ];
    }
}
