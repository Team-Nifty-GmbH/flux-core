<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Http\Requests\ReplicateOrderRequest;
use FluxErp\Models\Order;

class ReplicateOrder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new ReplicateOrderRequest())->rules();
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): Order
    {
        $originalOrder = Order::query()
            ->whereKey($this->data['id'])
            ->with('orderPositions')
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

        foreach ($originalOrder['order_positions'] ?? [] as $orderPosition) {
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
}
