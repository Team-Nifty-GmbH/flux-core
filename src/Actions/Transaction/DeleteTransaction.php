<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class DeleteTransaction implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:transactions,id',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'transaction.delete';
    }

    public static function description(): string|null
    {
        return 'delete transaction';
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function execute()
    {
        return Transaction::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
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
