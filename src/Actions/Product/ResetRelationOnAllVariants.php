<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ResetRelationOnAllVariantsRuleset;

class ResetRelationOnAllVariants extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return ResetRelationOnAllVariantsRuleset::class;
    }

    public function performAction(): int
    {
        $parent = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('parent_id'))
            ->firstOrFail();

        return $parent->resetRelationOnAllVariants(
            $this->getData('relation'),
            $this->getData('key')
        );
    }
}
