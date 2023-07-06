<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductOptionGroup implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateProductOptionGroupRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'product-option-group.update';
    }

    public static function description(): string|null
    {
        return 'update product option group';
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function execute(): Model
    {
        $productOptionGroup = ProductOptionGroup::query()
            ->whereKey($this->data['id'])
            ->first();

        $productOptionGroup->fill($this->data);
        $productOptionGroup->save();

        return $productOptionGroup->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductOptionGroup());

        $this->data = $validator->validate();

        return $this;
    }
}
