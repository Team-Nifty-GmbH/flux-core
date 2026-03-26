<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\OrderType\UpdateOrderTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateOrderType extends FluxAction
{
    public static function models(): array
    {
        return [OrderType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateOrderTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $tenants = Arr::pull($this->data, 'tenants');

        $orderType = resolve_static(OrderType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $orderType->fill($this->data);
        $orderType->save();

        if (! is_null($tenants)) {
            $orderType->tenants()->sync($tenants);
        }

        return $orderType->withoutRelations()->fresh();
    }
}
