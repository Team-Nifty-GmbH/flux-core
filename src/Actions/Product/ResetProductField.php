<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
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
            ->first();

        $variant->resetField($this->getData('field'));
        $variant->save();

        return $variant->refresh();
    }
}
