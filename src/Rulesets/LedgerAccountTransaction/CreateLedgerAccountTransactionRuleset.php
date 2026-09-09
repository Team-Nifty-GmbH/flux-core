<?php

namespace FluxErp\Rulesets\LedgerAccountTransaction;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Transaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateLedgerAccountTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'ledger_account_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'transaction_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Transaction::class]),
            ],
            'amount' => [
                'required',
                app(Numeric::class),
            ],
            'note' => 'string|max:255|nullable',
            'is_accepted' => 'boolean',
        ];
    }
}
