<?php

namespace FluxErp\Actions\Price;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreatePriceRequest;
use FluxErp\Models\Price;
use Illuminate\Support\Facades\Validator;

class CreatePrice implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreatePriceRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'price.create';
    }

    public static function description(): string|null
    {
        return 'create price';
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function execute(): Price
    {
        $price = new Price($this->data);
        $price->save();

        return $price;
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
