<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Rulesets\Product\ProductBundleProduct\DeleteProductBundleProductRuleset;

class DeleteProductBundleProduct extends FluxAction
{
    public static function models(): array
    {
        return [ProductBundleProduct::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductBundleProductRuleset::class;
    }

    public function performAction(): ?bool
    {
        $productBundleProduct = resolve_static(ProductBundleProduct::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        return $productBundleProduct->delete();
    }
}
