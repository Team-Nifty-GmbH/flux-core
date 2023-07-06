<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Support\Facades\Validator;

class CreateProductOptionGroup implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateProductOptionGroupRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'product-option-group.create';
    }

    public static function description(): string|null
    {
        return 'create product option group';
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function execute(): ProductOptionGroup
    {
        $productOptionGroup = new ProductOptionGroup($this->data);
        $productOptionGroup->save();

        return $productOptionGroup;
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
