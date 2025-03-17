<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductProperty;
use FluxErp\Rulesets\ProductProperty\CreateProductPropertyRuleset;

class CreateProductProperty extends FluxAction
{
    public static function models(): array
    {
        return [ProductProperty::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductPropertyRuleset::class;
    }

    public function performAction(): ProductProperty
    {
        $productProperty = app(ProductProperty::class, ['attributes' => $this->data]);
        $productProperty->save();

        return $productProperty->fresh();
    }
}
