<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\DiscountGroup;
use FluxErp\Rulesets\DiscountGroup\DeleteDiscountGroupRuleset;

class DeleteDiscountGroup extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteDiscountGroupRuleset::class;
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(DiscountGroup::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
