<?php

namespace FluxErp\Actions\Order;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateOrderRequest;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateOrder implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateOrderRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'order.create';
    }

    public static function description(): string|null
    {
        return 'create order';
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function execute(): Order
    {
        $this->data['currency_id'] = $this->data['currency_id'] ?? Currency::query()->first()?->id;
        $addresses = Arr::pull($this->data, 'addresses', []);

        $order = new Order($this->data);
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
        $order->save();

        if ($addresses) {
            $order->addresses()->attach($addresses);
        }

        return $order->refresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
