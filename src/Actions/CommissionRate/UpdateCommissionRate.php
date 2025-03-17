<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CommissionRate;
use FluxErp\Rulesets\CommissionRate\UpdateCommissionRateRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCommissionRate extends FluxAction
{
    public static function models(): array
    {
        return [CommissionRate::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateCommissionRateRuleset::class;
    }

    public function performAction(): Model
    {
        $commissionRate = resolve_static(CommissionRate::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $commissionRate->fill($this->data)
            ->save();

        return $commissionRate->withoutRelations()->fresh();
    }
}
