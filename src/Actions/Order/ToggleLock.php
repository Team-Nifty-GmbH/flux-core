<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Order\ToggleLockRuleset;

class ToggleLock extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return ToggleLockRuleset::class;
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): Order
    {
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->data['id'])
            ->first();
        $order->is_locked = data_get($this->data, 'is_locked', ! $order->is_locked);
        $order->save();

        return $order->withoutRelations()->fresh();
    }
}
