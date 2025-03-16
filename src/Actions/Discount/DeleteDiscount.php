<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Discount;
use FluxErp\Rulesets\Discount\DeleteDiscountRuleset;

class DeleteDiscount extends FluxAction
{
    public static function models(): array
    {
        return [Discount::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteDiscountRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Discount::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
