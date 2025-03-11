<?php

namespace FluxErp\Actions\ProductCrossSelling;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rulesets\ProductCrossSelling\CreateProductCrossSellingRuleset;

class CreateProductCrossSelling extends FluxAction
{
    public static function models(): array
    {
        return [ProductCrossSelling::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateProductCrossSellingRuleset::class;
    }

    public function performAction(): ProductCrossSelling
    {
        $products = $this->data['products'] ?? null;
        unset($this->data['products']);

        $productCrossSelling = app(ProductCrossSelling::class, ['attributes' => $this->data]);
        $productCrossSelling->save();

        if ($products) {
            $productCrossSelling->products()->attach($products);
        }

        return $productCrossSelling->fresh();
    }
}
