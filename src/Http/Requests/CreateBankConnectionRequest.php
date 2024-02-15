<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;

class CreateBankConnectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:bank_connections,uuid',
            'currency_id' => [
                'integer',
                'nullable',
                new ModelExists(Currency::class),
            ],
            'ledger_account_id' => [
                'integer',
                'nullable',
                new ModelExists(LedgerAccount::class),
            ],
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
