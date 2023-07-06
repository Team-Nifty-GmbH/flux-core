<?php

namespace FluxErp\Actions\Transaction;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateTransactionRequest;
use FluxErp\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateTransaction implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateTransactionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'transaction.update';
    }

    public static function description(): string|null
    {
        return 'update transaction';
    }

    public static function models(): array
    {
        return [Transaction::class];
    }

    public function execute(): Model
    {
        $warehouse = Transaction::query()
            ->whereKey($this->data['id'])
            ->first();

        $warehouse->fill($this->data);
        $warehouse->save();

        return $warehouse->withoutRelations()->fresh();
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
