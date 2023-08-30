<?php

namespace FluxErp\Actions\ProductCrossSelling;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateProductCrossSellingRequest;
use FluxErp\Models\ProductCrossSelling;

class CreateProductCrossSelling extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProductCrossSellingRequest())->rules();
    }

    public static function models(): array
    {
        return [ProductCrossSelling::class];
    }

    public function performAction(): ProductCrossSelling
    {
        $products = $this->data['products'] ?? null;
        unset($this->data['products']);

        $productCrossSelling = new ProductCrossSelling($this->data);
        $productCrossSelling->save();

        if ($products) {
            $productCrossSelling->products()->attach($products);
        }

        return $productCrossSelling->fresh();
    }
}
