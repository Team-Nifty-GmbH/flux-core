<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateProductPropertyRequest;
use FluxErp\Models\ProductProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateProductProperty extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProductPropertyRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function performAction(): Model
    {
        $productProperty = ProductProperty::query()
            ->whereKey($this->data['id'])
            ->first();

        $productProperty->fill($this->data);
        $productProperty->save();

        return $productProperty->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new ProductProperty());

        $this->data = $validator->validate();
    }
}
