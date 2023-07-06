<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateTransactionRequest;
use FluxErp\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class CreateTransaction implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateTransactionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'transaction.create';
    }

    public static function description(): string|null
    {
        return 'create transaction';
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
