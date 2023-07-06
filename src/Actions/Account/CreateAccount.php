<?php

namespace FluxErp\Actions\Account;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateAccountRequest;
use FluxErp\Models\Account;
use Illuminate\Support\Facades\Validator;

class CreateAccount implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateAccountRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'account.create';
    }

    public static function description(): string|null
    {
        return 'create account';
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function execute(): Account
    {
        $account = new Account($this->data);
        $account->save();

        return $account;
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
