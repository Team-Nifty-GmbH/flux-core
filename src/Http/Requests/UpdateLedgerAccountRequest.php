<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\LedgerAccountTypeEnum;
use Illuminate\Validation\Rules\Enum;

class UpdateLedgerAccountRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:ledger_accounts,id',
            'name' => 'sometimes|required|string|max:255',
            'number' => 'sometimes|required|numeric|max:255|unique:ledger_accounts,number',
            'description' => 'sometimes|nullable|string|max:255',
            'ledger_account_type_enum' => [
                'sometimes',
                'required',
                'string',
                new Enum(LedgerAccountTypeEnum::class),
            ],
            'is_automatic' => 'sometimes|boolean',
        ];
    }
}
