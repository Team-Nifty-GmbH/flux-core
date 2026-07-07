<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Jobs\SyncVariantInheritanceJob;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ResetProductFieldRuleset;

class ResetProductField extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return ResetProductFieldRuleset::class;
    }

    public function performAction(): Product
    {
        $variant = resolve_static(Product::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $field = $this->getData('field');

        $variant->resetField($field);
        $variant->save();

        // Re-copy the parent's current value (and translations) onto the variant now
        // that it's no longer flagged as overriding — otherwise the column stays stale.
        SyncVariantInheritanceJob::dispatchSync($variant->parent_id, [$field], [$variant->getKey()]);

        return $variant->refresh();
    }
}
