<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateProductPropertyRequest;
use FluxErp\Models\ProductProperty;
use Illuminate\Support\Facades\Validator;

class CreateProductProperty extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateProductPropertyRequest())->rules();
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

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductProperty());

        $this->data = $validator->validate();

        return $this;
    }
}
