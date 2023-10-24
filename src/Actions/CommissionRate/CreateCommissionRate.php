<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCommissionRateRequest;
use FluxErp\Models\CommissionRate;

class CreateCommissionRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = (new CreateCommissionRateRequest())->rules();
    }

    public static function models(): array
    {
        return [CommissionRate::class];
    }

    public function performAction(): CommissionRate
    {
        $commissionRate = new CommissionRate($this->data);
        $commissionRate->save();

        return $commissionRate->fresh();
    }
}
