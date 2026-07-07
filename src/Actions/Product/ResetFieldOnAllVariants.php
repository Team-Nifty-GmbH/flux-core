<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Jobs\SyncVariantInheritanceJob;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ResetFieldOnAllVariantsRuleset;

class ResetFieldOnAllVariants extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return ResetFieldOnAllVariantsRuleset::class;
    }

    public function performAction(): int
    {
        $parent = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('parent_id'))
            ->firstOrFail();

        $field = $this->getData('field');
        $touched = $parent->resetFieldOnAllVariants($field);

        if ($touched > 0) {
            // Re-copy the parent's current value (and translations) onto the now
            // un-overridden variants via the same set-based sync the job already uses.
            SyncVariantInheritanceJob::dispatchSync($parent->getKey(), [$field]);
        }

        return $touched;
    }
}
