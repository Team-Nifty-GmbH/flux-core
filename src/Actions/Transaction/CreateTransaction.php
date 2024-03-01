<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\CreateTransactionRuleset;

class CreateTransaction extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateTransactionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function performAction(): Transaction
    {
        $transaction = app(Transaction::class, ['attributes' => $this->data]);
        $transaction->save();

        return $transaction->fresh();
    }
}
