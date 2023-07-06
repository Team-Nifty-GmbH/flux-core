<?php

namespace FluxErp\Actions\Account;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateAccountRequest;
use FluxErp\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateAccount implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateAccountRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'account.update';
    }

    public static function description(): string|null
    {
        return 'update account';
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function execute(): Model
    {
        $account = Account::query()
            ->whereKey($this->data['id'])
            ->first();

        $account->fill($this->data);
        $account->save();

        return $account->withoutRelations()->fresh();
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
