<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\ContactBankConnection\CalculateContactBankConnectionBalance;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\CreateTransactionRuleset;

class CreateTransaction extends FluxAction
{
    public static function models(): array
    {
        return [Transaction::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTransactionRuleset::class;
    }

    public function performAction(): Transaction
    {
        $transaction = app(Transaction::class, ['attributes' => $this->data]);
        $transaction->save();

        if ($transaction->contact_bank_connection_id) {
            CalculateContactBankConnectionBalance::make([
                'id' => $transaction->contact_bank_connection_id,
            ])
                ->validate()
                ->execute();
        }

        return $transaction->fresh();
    }
}
