<?php

namespace FluxErp\Rulesets\LedgerAccountTransaction;

use FluxErp\Models\Pivots\LedgerAccountTransaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLedgerAccountTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerAccountTransaction::class]),
            ],
        ];
    }
}
