<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateBankConnectionRequest;
use FluxErp\Models\BankConnection;
use Illuminate\Support\Facades\Validator;

class CreateBankConnection implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateBankConnectionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'bank-connection.create';
    }

    public static function description(): string|null
    {
        return 'create bank connection';
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function execute(): BankConnection
    {
        $bankConnection = new BankConnection($this->data);
        $bankConnection->save();

        return $bankConnection;
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
