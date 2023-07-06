<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateCurrencyRequest;
use FluxErp\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateCurrency implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCurrencyRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'currency.update';
    }

    public static function description(): string|null
    {
        return 'update currency';
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function execute(): Model
    {
        $currency = Currency::query()
            ->whereKey($this->data['id'])
            ->first();

        $currency->fill($this->data);
        $currency->save();

        return $currency->withoutRelations()->fresh();
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
