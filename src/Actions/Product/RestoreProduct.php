<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\RestoreProductRuleset;

class RestoreProduct extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return RestoreProductRuleset::class;
    }

    public function performAction(): Product
    {
        /** @var Product $product */
        $product = resolve_static(Product::class, 'query')
            ->onlyTrashed()
            ->whereKey($this->getData('id'))
            ->first();
        $product->fill($this->getData());

        $product->restore();

        return $product->withoutRelations()->fresh();
    }
}
