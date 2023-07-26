<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateOrderRequest;
use FluxErp\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateOrder extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateOrderRequest())->rules();
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function execute(): Model
    {
        $addresses = Arr::pull($this->data, 'addresses', []);

        $order = Order::query()
            ->whereKey($this->data['id'])
            ->first();
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

        if (! ($this->data['address_delivery']['id'] ?? false) && $this->data['address_delivery_id'] ?? false) {
            $this->data['address_delivery_id'] = null;
        }

        $order->fill($this->data);
        $order->save();

        if ($addresses) {
            $order->addresses()->sync($addresses);
        }

        return $order->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Order());

        $this->data = $validator->validate();

        return $this;
    }
}
