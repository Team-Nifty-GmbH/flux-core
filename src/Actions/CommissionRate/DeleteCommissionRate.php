<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CommissionRate;
use FluxErp\Rulesets\CommissionRate\DeleteCommissionRateRuleset;

class DeleteCommissionRate extends FluxAction
{
    public static function models(): array
    {
        return [CommissionRate::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteCommissionRateRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(CommissionRate::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
