<?php

namespace FluxErp\Http\Requests;

class UpdateBankConnectionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:bank_connections,id',
            'currency_id' => 'integer|nullable|exists:currencies,id,deleted_at,NULL',
            'ledger_account_id' => 'integer|nullable|exists:ledger_accounts,id',
            'name' => 'sometimes|required|string|max:255',
            'account_holder' => 'sometimes|string|nullable',
            'bank_name' => 'sometimes|string|nullable',
            'bic' => 'sometimes|string|nullable',
            'credit_limit' => 'sometimes|numeric|nullable|min:0',
            'is_active' => 'boolean',
        ];
    }
}
