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

        unset(
            $originalOrder['id'],
            $originalOrder['uuid'],
            $originalOrder['parent_id'],
            $originalOrder['agent_id'],
            $originalOrder['bank_connection_id'],
            $originalOrder['address_invoice'],
            $originalOrder['address_delivery'],
            $originalOrder['state'],
            $originalOrder['payment_state'],
            $originalOrder['delivery_state'],
            $originalOrder['invoice_number'],
            $originalOrder['invoice_date'],
            $originalOrder['order_number'],
            $originalOrder['order_date'],
            $originalOrder['is_locked'],
            $originalOrder['is_imported'],
            $originalOrder['is_confirmed'],
            $originalOrder['is_paid'],
        );

        $orderData = array_merge(
            $originalOrder,
            $this->data,
        );

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
