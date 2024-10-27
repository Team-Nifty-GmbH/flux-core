<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderPosition;
use FluxErp\Rulesets\OrderPosition\DeleteOrderPositionRuleset;

class DeleteOrderPosition extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteOrderPositionRuleset::class;
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function performAction(): ?bool
    {
        $orderPosition = resolve_static(OrderPosition::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $orderPosition->children()->delete();

        return $orderPosition->delete();
    }
}
