<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\CreateOrderTypeRuleset;
use Illuminate\Support\Arr;

class CreateOrderType extends FluxAction
{
    public static function models(): array
    {
        return [OrderType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateOrderTypeRuleset::class;
    }

    public function performAction(): OrderType
    {
        $tenants = Arr::pull($this->data, 'tenants');

        $orderType = app(OrderType::class, ['attributes' => $this->data]);
        $orderType->save();

        if ($tenants) {
            $orderType->tenants()->attach($tenants);
        }

        return $orderType->fresh();
    }
}
