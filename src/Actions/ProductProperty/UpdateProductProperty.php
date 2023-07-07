<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateProductPropertyRequest;
use FluxErp\Models\ProductProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductProperty extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateProductPropertyRequest())->rules();
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

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductProperty());

        $this->data = $validator->validate();

        return $this;
    }
}
