<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
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
            ->whereKey($this->data['parent_id'])
            ->first();

        return $parent->resetFieldOnAllVariants($this->data['field']);
    }
}
