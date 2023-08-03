<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateTransactionRequest;
use FluxErp\Models\Transaction;

class CreateTransaction extends FluxAction
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

        return $transaction->fresh();
    }
}
