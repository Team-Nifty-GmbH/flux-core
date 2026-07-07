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

        $relation = $this->getData('relation');
        $key = $this->getData('key');

        $touched = $parent->resetRelationOnAllVariants($relation, $key);

        if ($touched > 0) {
            // Re-copy the parent's current relation row(s) back onto the now
            // non-owning variants — see ResetProductRelation::propagateFromParent().
            ResetProductRelation::propagateFromParent($parent, $relation, $key);
        }

        return $touched;
    }
}
