<?php

namespace FluxErp\Rulesets\LedgerAccount;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateLedgerAccountRuleset extends FluxRuleset
{
    protected static ?string $model = LedgerAccount::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:ledger_accounts,uuid',
            'name' => 'required|string|max:255',
            'number' => 'required|numeric|unique:ledger_accounts,number',
            'description' => 'nullable|string|max:255',
            'ledger_account_type_enum' => [
                'required',
                'string',
                Rule::enum(LedgerAccountTypeEnum::class),
            ],
            'is_automatic' => 'boolean',
        ];
    }
}
