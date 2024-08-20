<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductProperty;
use FluxErp\Rulesets\ProductProperty\UpdateProductPropertyRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateProductProperty extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateProductPropertyRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ProductProperty::class];
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
