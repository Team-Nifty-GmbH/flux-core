<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateProductOptionRequest;
use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductOption implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateProductOptionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'product-option.update';
    }

    public static function description(): string|null
    {
        return 'update product option';
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function execute(): Model
    {
        $productOption = ProductOption::query()
            ->whereKey($this->data['id'])
            ->first();

        $productOption->fill($this->data);
        $productOption->save();

        return $productOption->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOption());

        $this->data = $validator->validate();

        return $this;
    }
}
