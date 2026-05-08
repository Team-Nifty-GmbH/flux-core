<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ResetProductRelationRuleset;

class ResetProductRelation extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return ResetProductRelationRuleset::class;
    }

    public function performAction(): Product
    {
        $variant = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $variant->resetRelation($this->getData('relation'), $this->getData('key'));

        return $variant->refresh();
    }
}
