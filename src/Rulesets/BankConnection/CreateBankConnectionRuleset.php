<?php

namespace FluxErp\Rulesets\BankConnection;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rules\Iban;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;

class CreateBankConnectionRuleset extends FluxRuleset
{
    protected static ?string $model = BankConnection::class;

    public static function getRules(): array
    {
        return array_merge(
            resolve_static(BankConnectionRuleset::class, 'getRules'),
            parent::getRules()
        );
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:bank_connections,uuid',
            'currency_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Currency::class]),
            ],
            'ledger_account_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'name' => 'required|string|max:255',
            'iban' => ['nullable', 'string', app(Iban::class), 'unique:bank_connections,iban'],
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_virtual' => 'boolean',
        ];
    }
}
