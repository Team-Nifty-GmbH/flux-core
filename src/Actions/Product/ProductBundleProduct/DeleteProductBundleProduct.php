<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Rulesets\Product\ProductBundleProduct\DeleteProductBundleProductRuleset;

class DeleteProductBundleProduct extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteProductBundleProductRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ProductBundleProduct::class];
    }

    public function performAction(): ?bool
    {
        $productBundleProduct = app(ProductBundleProduct::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        return $productBundleProduct->delete();
    }
}
