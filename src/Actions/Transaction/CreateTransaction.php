<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\ContactBankConnection\CalculateContactBankConnectionBalance;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Transaction;
use FluxErp\Rulesets\Transaction\CreateTransactionRuleset;
use Illuminate\Support\Str;

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

    protected function prepareForValidation(): void
    {
        $this->data['counterpart_iban'] = is_string($this->getData('counterpart_iban'))
            ? Str::of($this->getData('counterpart_iban'))->upper()->remove(' ')->toString()
            : $this->getData('counterpart_iban');

        $this->data['counterpart_bic'] = is_string($this->getData('counterpart_bic'))
            ? Str::of($this->getData('counterpart_bic'))->upper()->remove(' ')->toString()
            : $this->getData('counterpart_bic');
    }
}
