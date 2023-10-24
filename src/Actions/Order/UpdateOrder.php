<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Http\Requests\UpdateOrderRequest;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateOrder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateOrderRequest())->rules();
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): Model
    {
        $addresses = Arr::pull($this->data, 'addresses', []);
        $users = Arr::pull($this->data, 'users');

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

        if (! ($this->data['address_delivery']['id'] ?? false)) {
            $this->data['address_delivery_id'] = null;
        } else {
            $this->data['address_delivery_id'] = $this->data['address_delivery']['id'];
        }

        $order->fill($this->data);
        $order->save();

        if ($addresses) {
            $order->addresses()->sync($addresses);
        }

        if (! is_null($users)) {
            $order->users()->sync($users);
        }

        return $order->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Order());

        $this->data = $validator->validate();

        $order = Order::query()
            ->whereKey($this->data['id'])
            ->first();

        $updatedOrderType = false;
        if ($this->data['order_type_id'] ?? false) {
            $updatedOrderType = $order->order_type_id !== $this->data['order_type_id'] ?
                $this->data['order_type_id'] : false;
        }

        if (($this->data['invoice_number'] ?? false)
            || $updatedOrderType
        ) {
            $isPurchase = OrderType::query()
                ->whereKey($updatedOrderType ?: $order->order_type_id)
                ->whereIn('order_type_enum', [OrderTypeEnum::Purchase->value, OrderTypeEnum::PurchaseRefund->value])
                ->exists();

            if (Order::query()
                ->where('id', '!=', $this->data['id'])
                ->where('client_id', $order->client_id)
                ->where('invoice_number', $this->data['invoice_number'] ?? $order->invoice_number)
                ->when($isPurchase, fn (Builder $query) => $query->where('contact_id', $order->contact_id))
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'invoice_number' => [__('validation.unique', ['attribute' => 'invoice_number'])],
                ])->errorBag('createOrder');
            }
        }
    }
}
