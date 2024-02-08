<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rules\ModelExists;

class UpdateBankConnectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(BankConnection::class),
            ],
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
            'name' => 'sometimes|required|string|max:255',
            'account_holder' => 'string|nullable',
            'bank_name' => 'string|nullable',
            'bic' => 'string|nullable',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
