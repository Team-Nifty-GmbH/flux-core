<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Rulesets\Product\ProductBundleProduct\DeleteProductBundleProductRuleset;

class DeleteProductBundleProduct extends FluxAction
{
    public static function models(): array
    {
        return [BundleProductProduct::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductBundleProductRuleset::class;
    }

    public function performAction(): ?bool
    {
        $productBundleProduct = resolve_static(BundleProductProduct::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        return $productBundleProduct->delete();
    }
}
