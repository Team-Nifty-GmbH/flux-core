<?php

namespace FluxErp\Http\Requests;

class UpdateBankConnectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:bank_connections,id',
            'currency_id' => 'integer|nullable|exists:currencies,id,deleted_at,NULL',
            'ledger_account_id' => 'integer|nullable|exists:ledger_accounts,id',
            'name' => 'sometimes|required|string|max:255',
            'account_holder' => 'string|nullable',
            'bank_name' => 'string|nullable',
            'bic' => 'string|nullable',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
