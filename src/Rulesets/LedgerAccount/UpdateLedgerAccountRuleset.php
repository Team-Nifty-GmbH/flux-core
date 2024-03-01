<?php

namespace FluxErp\Rulesets\LedgerAccount;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rules\Enum;

class UpdateLedgerAccountRuleset extends FluxRuleset
{
    protected static ?string $model = LedgerAccount::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(LedgerAccount::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'number' => 'sometimes|required|numeric|unique:ledger_accounts,number',
            'description' => 'nullable|string|max:255',
            'ledger_account_type_enum' => [
                'sometimes',
                'required',
                'string',
                new Enum(LedgerAccountTypeEnum::class),
            ],
            'is_automatic' => 'boolean',
        ];
    }
}
