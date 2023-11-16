<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Http\Requests\CreateOrderRequest;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateOrder extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateOrderRequest())->rules();
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function performAction(): Order
    {
        $this->data['currency_id'] = $this->data['currency_id']
            ?? Currency::query()
                ->where('is_default', true)
                ->first()
                ?->id;
        $addresses = Arr::pull($this->data, 'addresses', []);
        $addressInvoice = Address::query()->whereKey($this->data['address_invoice_id'])->first();

        if (! ($this->data['address_delivery']['id'] ?? false) && ! ($this->data['address_delivery_id'] ?? false)) {
            $this->data['address_delivery_id'] = null;
        } elseif ($this->data['address_delivery']['id'] ?? false) {
            $this->data['address_delivery_id'] = $this->data['address_delivery']['id'];
        }

        $contactId = $this->data['contact_id'] ?? $addressInvoice?->contact_id;
        $contact = Contact::query()->whereKey($contactId)->first();

        $this->data['agent_id'] = $this->data['agent_id'] ?? $contact->agent_id;
        $this->data['approval_user_id'] = $this->data['approval_user_id'] ?? $contact->approval_user_id;
        $this->data['bank_connection_id'] = $this->data['bank_connection_id']
            ?? $contact->bankConnections()->first()?->id;
        $this->data['payment_target'] = $this->data['payment_target'] ?? $contact->payment_target_days;
        $this->data['payment_discount_target'] = $this->data['payment_discount_target'] ?? $contact->discount_days;
        $this->data['payment_discount_percent'] = $this->data['payment_discount_percent'] ?? $contact->discount_percent;
        $this->data['payment_reminder_days_1'] = $this->data['payment_reminder_days_1']
            ?? $contact->payment_reminder_days_1;
        $this->data['payment_reminder_days_2'] = $this->data['payment_reminder_days_2']
            ?? $contact->payment_reminder_days_2;
        $this->data['payment_reminder_days_3'] = $this->data['payment_reminder_days_3']
            ?? $contact->payment_reminder_days_3;

        $this->data['order_date'] = $this->data['order_date'] ?? now();

        $this->data['contact_id'] = $contactId;

        $users = Arr::pull($this->data, 'users');

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

        if ($users) {
            $order->users()->sync($users);
        }

        return $order->refresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Order());

        $this->data = $validator->validate();

        if ($this->data['invoice_number'] ?? false) {
            $isPurchase = OrderType::query()
                ->whereKey($this->data['order_type_id'])
                ->whereIn('order_type_enum', [OrderTypeEnum::Purchase->value, OrderTypeEnum::PurchaseRefund->value])
                ->exists();

            if ($isPurchase && ! ($this->data['contact_id'] ?? false)) {
                throw ValidationException::withMessages([
                    'contact_id' => [__('validation.required', ['attribute' => 'contact_id'])],
                ])->errorBag('createOrder');
            }

            if (Order::query()
                ->where('client_id', $this->data['client_id'])
                ->where('invoice_number', $this->data['invoice_number'])
                ->when($isPurchase, fn (Builder $query) => $query->where('contact_id', $this->data['contact_id']))
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'invoice_number' => [__('validation.unique', ['attribute' => 'invoice_number'])],
                ])->errorBag('createOrder');
            }
        }
    }
}
