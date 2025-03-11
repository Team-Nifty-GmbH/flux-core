<?php

namespace FluxErp\Rulesets\BankConnection;

use FluxErp\Models\BankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\LedgerAccount;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\ContactBankConnection\BankConnectionRuleset;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Support\Arr;

class UpdateBankConnectionRuleset extends FluxRuleset
{
    protected static ?string $model = BankConnection::class;

    public static function getRules(): array
    {
        return array_merge(
            Arr::except(
                resolve_static(BankConnectionRuleset::class, 'getRules'),
                'iban'
            ),
            parent::getRules()
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => BankConnection::class]),
            ],
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
            'name' => 'sometimes|required|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
