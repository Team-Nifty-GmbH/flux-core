<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Commission;
use FluxErp\Rulesets\Commission\DeleteCommissionRuleset;

class DeleteCommission extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteCommissionRuleset::class;
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Commission::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
