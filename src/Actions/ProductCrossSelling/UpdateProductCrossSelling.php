<?php

namespace FluxErp\Actions\ProductCrossSelling;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rulesets\ProductCrossSelling\UpdateProductCrossSellingRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateProductCrossSelling extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateProductCrossSellingRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ProductCrossSelling::class];
    }

    public function performAction(): ProductCrossSelling|Model
    {
        $products = $this->data['products'] ?? null;
        unset($this->data['products']);

        $productCrossSelling = resolve_static(ProductCrossSelling::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $productCrossSelling->fill($this->data);
        $productCrossSelling->save();

        if (! is_null($products)) {
            $productCrossSelling->products()->sync($products);
        }

        return $productCrossSelling->withoutRelations()->fresh();
    }
}
