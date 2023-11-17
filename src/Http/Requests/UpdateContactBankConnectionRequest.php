<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\Iban;

class UpdateContactBankConnectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:contact_bank_connections,id,deleted_at,NULL',
            'contact_id' => 'integer|nullable|exists:contacts,id,deleted_at,NULL',
            'iban' => ['string', new Iban()],
            'account_holder' => 'string|nullable',
            'bank_name' => 'string|nullable',
            'bic' => 'string|nullable',
        ];
    }
}
