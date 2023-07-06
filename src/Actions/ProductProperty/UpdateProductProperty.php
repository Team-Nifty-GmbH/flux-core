<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateProductPropertyRequest;
use FluxErp\Models\ProductProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductProperty implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateProductPropertyRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'product-property.update';
    }

    public static function description(): string|null
    {
        return 'update product property';
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function execute(): Model
    {
        $productProperty = ProductProperty::query()
            ->whereKey($this->data['id'])
            ->first();

        $productProperty->fill($this->data);
        $productProperty->save();

        return $productProperty->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductProperty());

        $this->data = $validator->validate();

        return $this;
    }
}
