<?php

namespace FluxErp\Actions\Account;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Account;
use Illuminate\Support\Facades\Validator;

class DeleteAccount implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:accounts,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'account.delete';
    }

    public static function description(): string|null
    {
        return 'delete account';
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function execute(): bool|null
    {
        return Account::query()
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
