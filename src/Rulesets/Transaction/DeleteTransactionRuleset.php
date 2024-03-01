<?php

namespace FluxErp\Rulesets\Transaction;

use FluxErp\Models\Transaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTransactionRuleset extends FluxRuleset
{
    protected static ?string $model = Transaction::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Transaction::class),
            ],
        ];
    }
}
