<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateTransactionRequest;
use FluxErp\Models\Transaction;

class CreateTransaction extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateTransactionRequest())->rules();
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function performAction(): Transaction
    {
        $transaction = new Transaction($this->data);
        $transaction->save();

        return $transaction;
    }
}
