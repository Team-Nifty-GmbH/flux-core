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
        $order->is_locked = ! $order->is_locked;
        $order->save();

        return $order->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $orderPositions = data_get($this->data, 'order_positions', []);
        $ids = array_column($orderPositions ?? [], 'id');

        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'order_positions' => ['No duplicate order position ids allowed.'],
            ])->errorBag('replicateOrder');
        }

        if ($orderPositions) {
            if (resolve_static(OrderPosition::class, 'query')
                ->whereIntegerInRaw('id', $ids)
                ->where('order_id', '!=', $this->data['id'])
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'order_positions' => ['Only order positions from given order allowed.'],
                ])->errorBag('replicateOrder');
            }
        }
    }
}
