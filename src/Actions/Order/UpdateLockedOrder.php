<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Order;
use FluxErp\Rulesets\Order\UpdateLockedOrderRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateLockedOrder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateLockedOrderRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): Model
    {
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $order->fill($this->data);
        $order->save();

        return $order->withoutRelations()->fresh();
    }
}
