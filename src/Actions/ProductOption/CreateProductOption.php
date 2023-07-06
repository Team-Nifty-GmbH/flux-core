<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateProductOptionRequest;
use FluxErp\Models\ProductOption;
use Illuminate\Support\Facades\Validator;

class CreateProductOption implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateProductOptionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'product-option.create';
    }

    public static function description(): string|null
    {
        return 'create product option';
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function execute(): ProductOption
    {
        $productOption = new ProductOption($this->data);
        $productOption->save();

        return $productOption;
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
