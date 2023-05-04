<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateOrderRequest;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderService
{
    public function create(array $data): Order
    {
        $data['currency_id'] = $data['currency_id'] ?? Currency::query()->first()?->id;

        $order = new Order($data);
        $this->fillPriceCalculation($order);
        unset($order->addresses);
        $order->save();

        if ($data['addresses'] ?? false) {
            $order->addresses()->attach($data['addresses']);
        }

        return $order->refresh();
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateOrderRequest(),
            model: new Order()
        );

        foreach ($data as $item) {
            $order = Order::query()
                ->whereKey($item['id'])
                ->first();

            $order->fill($item);
            $this->fillPriceCalculation($order);
            unset($order->addresses);
            $order->save();

            if ($item['addresses'] ?? false) {
                $order->addresses()->sync($item['addresses']);
            }

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $order->withoutRelations()->fresh(),
                additions: ['id' => $order->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'orders updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $order = Order::query()
            ->whereKey($id)
            ->first();

        if (! $order) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'order not found']
            );
        }

        if ($order->is_locked) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['is_locked' => 'order is locked']
            );
        }

        if ($order->children->count() > 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['children' => 'order has children']
            );
        }

        $order->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'order deleted'
        );
    }

    private function fillPriceCalculation(Model $order): void
    {
        // Calculate shipping costs if given
        if ($order->shipping_costs_net_price) {
            $order->shipping_costs_vat_rate_percentage = 0.190000000;   // TODO: Make this percentage NOT hardcoded!
            $order->shipping_costs_gross_price = net_to_gross(
                $order->shipping_costs_net_price,
                $order->shipping_costs_vat_rate_percentage
            );
            $order->shipping_costs_vat_price = bcsub(
                $order->shipping_costs_gross_price,
                $order->shipping_costs_net_price
            );
        }
    }
}
