<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductProperty;
use FluxErp\Rulesets\ProductProperty\CreateProductPropertyRuleset;
use Illuminate\Support\Facades\Validator;

class CreateProductProperty extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateProductPropertyRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function performAction(): ProductProperty
    {
        $productProperty = app(ProductProperty::class, ['attributes' => $this->data]);
        $productProperty->save();

        return $productProperty->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(ProductProperty::class));

        $this->data = $validator->validate();
    }
}
