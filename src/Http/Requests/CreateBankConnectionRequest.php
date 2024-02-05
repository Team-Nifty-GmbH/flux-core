<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\Iban;

class CreateBankConnectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:bank_connections,uuid',
            'currency_id' => 'integer|nullable|exists:currencies,id,deleted_at,NULL',
            'ledger_account_id' => 'integer|nullable|exists:ledger_accounts,id',
            'name' => 'required|string|max:255',
            'account_holder' => 'string|nullable',
            'bank_name' => 'string|nullable',
            'iban' => ['nullable', 'string', new Iban(), 'unique:bank_connections,iban'],
            'bic' => 'string|nullable',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
