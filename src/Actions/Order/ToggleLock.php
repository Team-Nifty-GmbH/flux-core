<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Rulesets\Order\ToggleLockRuleset;
use Illuminate\Validation\ValidationException;

class ToggleLock extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(ToggleLockRuleset::class, 'getRules');
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
