<?php

namespace FluxErp\Rulesets\LedgerAccount;

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Models\Client;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateLedgerAccountRuleset extends FluxRuleset
{
    protected static ?string $model = LedgerAccount::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:ledger_accounts,uuid',
            'client_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
            'name' => 'required|string|max:255',
            'number' => 'required|numeric',
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
