<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Rulesets\Order\ReplicateOrderRuleset;
use Illuminate\Validation\ValidationException;

class ReplicateOrder extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return ReplicateOrderRuleset::class;
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): Order
    {
        $getOrderPositionsFromOrigin = is_null(data_get($this->data, 'order_positions'));

        $originalOrder = resolve_static(Order::class, 'query')
            ->whereKey($this->data['id'])
            ->when($getOrderPositionsFromOrigin, fn ($query) => $query->with('orderPositions'))
            ->first()
            ->toArray();

        $orderData = array_merge(
            $originalOrder,
            $this->data,
        );

        unset(
            $orderData['id'],
            $orderData['uuid'],
            $orderData['agent_id'],
            $orderData['bank_connection_id'],
            $orderData['address_invoice'],
            $orderData['address_delivery'],
            $orderData['state'],
            $orderData['payment_state'],
            $orderData['delivery_state'],
            $orderData['payment_reminder_current_level'],
            $orderData['payment_reminder_next_date'],
            $orderData['invoice_number'],
            $orderData['invoice_date'],
            $orderData['order_number'],
            $orderData['order_date'],
            $orderData['is_locked'],
            $orderData['is_imported'],
            $orderData['is_confirmed'],
            $orderData['is_paid'],
        );

        if ($originalOrder['parent_id'] === $orderData['parent_id']) {
            unset($orderData['parent_id']);
        }

        $order = CreateOrder::make($orderData)
            ->checkPermission()
            ->validate()
            ->execute();

        if (! $getOrderPositionsFromOrigin) {
            $replicateOrderPositions = collect($this->data['order_positions']);
            $orderPositions = resolve_static(OrderPosition::class, 'query')
                ->whereIntegerInRaw('id', array_column($this->data['order_positions'], 'id'))
                ->get()
                ->map(function (OrderPosition $orderPosition) use ($replicateOrderPositions) {
                    $position = $replicateOrderPositions->first(fn ($item) => $item['id'] === $orderPosition->id);

                    $orderPosition->origin_position_id = $orderPosition->id;
                    $orderPosition->amount = $position['amount'];

                    return $orderPosition;
                })
                ->toArray();
        } else {
            $orderPositions = $originalOrder['order_positions'] ?? [];
        }

        foreach ($orderPositions as $orderPosition) {
            $orderPosition['order_id'] = $order->id;

            unset(
                $orderPosition['id'],
                $orderPosition['uuid'],
                $orderPosition['amount_packed_products'],
            );

            CreateOrderPosition::make($orderPosition)
                ->checkPermission()
                ->validate()
                ->execute();
        }

        $order->calculatePrices()->save();

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
