<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateTransactionRequest;
use FluxErp\Models\Transaction;

class CreateTransaction extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateTransactionRequest())->rules();
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function execute(): Transaction
    {
        $transaction = new Transaction($this->data);
        $transaction->save();

        return $transaction;
    }
}
