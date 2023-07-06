<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateProductPropertyRequest;
use FluxErp\Models\ProductProperty;
use Illuminate\Support\Facades\Validator;

class CreateProductProperty implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateProductPropertyRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'product-property.create';
    }

    public static function description(): string|null
    {
        return 'create product property';
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function execute(): ProductProperty
    {
        $productProperty = new ProductProperty($this->data);
        $productProperty->save();

        return $productProperty;
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
