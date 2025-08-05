<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductProperty;
use FluxErp\Rulesets\ProductProperty\UpdateProductPropertyRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateProductProperty extends FluxAction
{
    public static function models(): array
    {
        return [ProductProperty::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductPropertyRuleset::class;
    }

    public function performAction(): Model
    {
        $productProperty = resolve_static(ProductProperty::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $productProperty->fill($this->data);
        $productProperty->save();

        return $productProperty->withoutRelations()->fresh();
    }
}
