<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CommissionRate;
use FluxErp\Rulesets\CommissionRate\CreateCommissionRateRuleset;

class CreateCommissionRate extends FluxAction
{
    public static function models(): array
    {
        return [CommissionRate::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCommissionRateRuleset::class;
    }

    public function performAction(): CommissionRate
    {
        $commissionRate = app(CommissionRate::class, ['attributes' => $this->data]);
        $commissionRate->save();

        return $commissionRate->fresh();
    }
}
