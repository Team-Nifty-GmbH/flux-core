<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\PromoteParentToStandaloneRuleset;

class PromoteParentToStandalone extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return PromoteParentToStandaloneRuleset::class;
    }

    public function performAction(): Product
    {
        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $product->is_variant_parent = false;
        $product->save();

        return $product->refresh();
    }
}
