<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCommissionRequest;
use FluxErp\Models\Commission;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\OrderPosition;

class CreateCommission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = (new CreateCommissionRequest())->rules();
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): Commission
    {
        if (! array_key_exists('commission', $this->data)) {
            $commissionRate = CommissionRate::query()
                ->whereKey($this->data['commission_rate_id'])
                ->first();

            $orderPosition = OrderPosition::query()
                ->whereKey($this->data['order_position_id'])
                ->first();

            $price = $orderPosition->is_net ? $orderPosition->total_net_price : $orderPosition->total_gross_price;
            $this->data['commission'] = bcround(bcmul($price, $commissionRate->commission_rate), 2);
        }

        $commission = new Commission($this->data);
        $commission->save();

        return $commission->fresh();
    }
}
