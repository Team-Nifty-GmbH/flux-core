<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateBankConnectionRequest;
use FluxErp\Models\BankConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateBankConnection implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateBankConnectionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'bank-connection.update';
    }

    public static function description(): string|null
    {
        return 'update bank connection';
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function execute(): Model
    {
        $bankConnection = BankConnection::query()
            ->whereKey($this->data['id'])
            ->first();

        $bankConnection->fill($this->data);
        $bankConnection->save();

        return $bankConnection->withoutRelations()->fresh();
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
