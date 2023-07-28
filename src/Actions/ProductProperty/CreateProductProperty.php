<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateProductPropertyRequest;
use FluxErp\Models\ProductProperty;
use Illuminate\Support\Facades\Validator;

class CreateProductProperty extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProductPropertyRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function performAction(): ProductProperty
    {
        $productProperty = new ProductProperty($this->data);
        $productProperty->save();

        return $productProperty;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductProperty());

        $this->data = $validator->validate();
    }
}
