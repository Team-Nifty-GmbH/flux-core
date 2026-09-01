<?php

namespace FluxErp\Rulesets\LedgerAccountTransaction;

use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Pivots\LedgerAccountTransaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLedgerAccountTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccountTransaction::class]),
            ],
            'ledger_account_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccount::class]),
            ],
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class),
            ],
            'note' => 'string|max:255|nullable',
            'is_accepted' => 'boolean',
        ];
    }
}
