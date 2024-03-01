<?php

namespace FluxErp\Actions\ProductCrossSelling;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Rulesets\ProductCrossSelling\CreateProductCrossSellingRuleset;

class CreateProductCrossSelling extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateProductCrossSellingRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ProductCrossSelling::class];
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
