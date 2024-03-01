<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CommissionRate;
use FluxErp\Rulesets\CommissionRate\CreateCommissionRateRuleset;

class CreateCommissionRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = resolve_static(CreateCommissionRateRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [CommissionRate::class];
    }

    public function performAction(): CommissionRate
    {
        $commissionRate = app(CommissionRate::class, ['attributes' => $this->data]);
        $commissionRate->save();

        return $commissionRate->fresh();
    }
}
