<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\Iban;

class CreateBankConnectionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:bank_connections,uuid',
            'currency_id' => 'integer|nullable|exists:currencies,id,deleted_at,NULL',
            'ledger_account_id' => 'integer|nullable|exists:ledger_accounts,id',
            'name' => 'required|string|max:255',
            'account_holder' => 'sometimes|string|nullable',
            'bank_name' => 'sometimes|string|nullable',
            'iban' => ['nullable', 'string', new Iban(), 'unique:bank_connections,iban'],
            'bic' => 'sometimes|string|nullable',
            'credit_limit' => 'sometimes|numeric|nullable|min:0',
            'is_active' => 'boolean',
        ];
    }
}
