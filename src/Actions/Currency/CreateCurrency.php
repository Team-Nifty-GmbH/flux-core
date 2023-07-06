<?php

namespace FluxErp\Actions\Currency;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCurrencyRequest;
use FluxErp\Models\Currency;
use Illuminate\Support\Facades\Validator;

class CreateCurrency implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCurrencyRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'currency.create';
    }

    public static function description(): string|null
    {
        return 'create currency';
    }

    public static function models(): array
    {
        return [Currency::class];
    }

    public function execute(): Currency
    {
        $currency = new Currency($this->data);
        $currency->save();

        return $currency;
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
