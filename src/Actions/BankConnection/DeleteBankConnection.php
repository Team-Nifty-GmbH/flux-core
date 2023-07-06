<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\BankConnection;
use Illuminate\Support\Facades\Validator;

class DeleteBankConnection implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:bank_connections,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'bank-connection.delete';
    }

    public static function description(): string|null
    {
        return 'delete bank connection';
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function execute()
    {
        return BankConnection::query()
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
