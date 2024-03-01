<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CommissionRate;
use FluxErp\Rulesets\CommissionRate\UpdateCommissionRateRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCommissionRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = resolve_static(UpdateCommissionRateRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [CommissionRate::class];
    }

    public function performAction(): Model
    {
        $commissionRate = app(CommissionRate::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $commissionRate->fill($this->data)
            ->save();

        return $commissionRate->withoutRelations()->fresh();
    }
}
