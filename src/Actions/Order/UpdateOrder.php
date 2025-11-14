<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Rulesets\Order\UpdateOrderRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateOrder extends FluxAction
{
    public static function models(): array
    {
        return [Order::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateOrderRuleset::class;
    }

    public function performAction(): Model
    {
        $addresses = Arr::pull($this->data, 'addresses');
        $users = Arr::pull($this->data, 'users');

        $order = resolve_static(Order::class, 'query')
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

        $approvalUserId = $this->getData('approval_user_id', $order->approval_user_id);
        if ($approvalUserId !== $order->approval_user_id) {
            $order->approvalUser?->unsubscribeNotificationChannel($order->broadcastChannel());
        }

        $order->fill($this->data);
        $order->save();

        if (! is_null($addresses)) {
            $addresses = collect($addresses)
                ->unique(fn ($address) => $address['address_id'] . '_' . $address['address_type_id'])
                ->keyBy('address_id')
                ->toArray();

            $order->addresses()->sync($addresses);
        }

        if (! is_null($users)) {
            $order->users()->sync($users);
        }

        return $order->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($order->is_locked) {
            $errors += [
                'is_locked' => ['Order is locked'],
            ];
        }

        $updatedOrderType = false;
        if ($this->data['order_type_id'] ?? false) {
            $updatedOrderType = $order->order_type_id !== $this->data['order_type_id'] ?
                $this->data['order_type_id'] : false;
        }

        if (($this->data['invoice_number'] ?? false)
            || $updatedOrderType
        ) {
            $isPurchase = resolve_static(OrderType::class, 'query')
                ->whereKey($updatedOrderType ?: $order->order_type_id)
                ->whereIn('order_type_enum', [OrderTypeEnum::Purchase->value, OrderTypeEnum::PurchaseRefund->value])
                ->exists();

            if (resolve_static(Order::class, 'query')
                ->where('id', '!=', $this->data['id'])
                ->where('tenant_id', $order->tenant_id)
                ->where('invoice_number', $this->data['invoice_number'] ?? $order->invoice_number)
                ->when($isPurchase, fn (Builder $query) => $query->where('contact_id', $order->contact_id))
                ->exists()
            ) {
                $errors += [
                    'invoice_number' => ['Invoice number already exists'],
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateOrder');
        }
    }
}
