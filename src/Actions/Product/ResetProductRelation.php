<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ResetProductRelationRuleset;
use FluxErp\Support\VariantInheritance\PivotInheritanceSync;

class ResetProductRelation extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    /**
     * Re-copy the parent's current relation row(s) back onto its children as
     * is_inherited=true, now that a reset variant no longer owns them.
     *
     * Neither Price::booted()'s save propagation nor
     * PivotInheritanceSync::propagateToChildren() can be scoped to a single
     * variant, so this re-syncs every non-owning child of the parent rather
     * than just the one that was reset. Both are idempotent no-ops for
     * children already in sync, so this is harmless — just slightly more
     * work than strictly necessary.
     */
    public static function propagateFromParent(Product $parent, string $relation, mixed $key): void
    {
        if ($relation === 'prices') {
            $parent->ownPrices()
                ->when($key !== null, fn ($query) => $query->where('price_list_id', $key))
                ->get()
                ->each->save();

            return;
        }

        PivotInheritanceSync::propagateToChildren($parent);
    }

    protected function getRulesets(): string|array
    {
        return ResetProductRelationRuleset::class;
    }

    public function performAction(): Product
    {
        $variant = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $relation = $this->getData('relation');
        $key = $this->getData('key');

        $variant->resetRelation($relation, $key);

        $parent = $variant->parent()->first();

        if ($parent) {
            static::propagateFromParent($parent, $relation, $key);
        }

        return $variant->refresh();
    }
}
